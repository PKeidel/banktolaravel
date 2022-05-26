<?php

namespace PKeidel\BankToLaravel\Commands;

use Carbon\Carbon;
use DateTime;
use Exception;
use Fhp\Action\GetSEPAAccounts;
use Fhp\Action\GetStatementOfAccount;
use Fhp\BaseAction;
use Fhp\CurlException;
use Fhp\Model\SEPAAccount;
use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Model\StatementOfAccount\Transaction;
use Fhp\Model\TanRequestChallengeImage;
use Fhp\Protocol\ServerException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PKeidel\BankToLaravel\Events\NewEntry;
use PKeidel\BankToLaravel\Models\Bookings;

class ImportEntries extends Command {
	protected $signature = 'bank:import';
	protected $description = 'Reads booking informations from an bank account and saves it to a local table in the database';

    private $fints;

    public function handle() {
        $this->createFints();

        $from = new DateTime(env('FHP_BANK_START', '7 day ago'));
        $to   = new DateTime();

        $allowedAccounts = explode(',', env('FHP_BANK_ACCOUNT', ''));

        try {
            $getSepaAccounts = GetSEPAAccounts::create();
            $this->fints->execute($getSepaAccounts);
            if ($getSepaAccounts->needsTan()) {
                $this->handleTan($getSepaAccounts);
            }
            $accounts = $getSepaAccounts->getAccounts();
            foreach($accounts as $account) {
                if(!in_array($account->getAccountNumber(), $allowedAccounts, true)) {
                    continue;
                }

                $getStatement = GetStatementOfAccount::create($account, $from, $to);
                $this->fints->execute($getStatement);

                if ($getStatement->needsTan()) {
                    Log::info("TAN input required!");
                    $this->handleTan($getStatement);
                }

                $soa = $getStatement->getStatement();
                foreach ($soa->getStatements() as $statement) {
                    $saldo = $statement->getCreditDebit() == Statement::CD_DEBIT ? 0 - $statement->getStartBalance() : $statement->getStartBalance();
                    foreach ($statement->getTransactions() as $transaction) {
                        if(env('FHP_SAVE_TO_DB', true)) {
                            $booking = Bookings::updateOrCreate([
                                'ref_iban' => $account->getIban(),
                                'valutadate' => $transaction->getValutaDate(),
                                'bookingdate' => $transaction->getBookingDate(),
                                'amount' => $transaction->getAmount(),
                                'creditdebit' => $transaction->getCreditDebit(),
                                'bookingtext' => $transaction->getBookingText(),
                                'accountnumber' => $transaction->getAccountNumber(),
                                'bankcode' => $transaction->getBankCode(),
                            ], [
                                'description1' => $transaction->getDescription1(),
                                'structureddescription' => $transaction->getStructuredDescription(),
                                'name' => $transaction->getName(),
                            ]);
                            if($this->wasModelRecentlyCreated($booking)) {
                                Log::info("    => this one was new, so fire an NewEntry event");
                                $this->fireEvent($account, $transaction, $saldo);
                            }
                        } else {
                            $this->fireEvent($account, $transaction, $saldo);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::error($e);
            echo $e::class . ': ' .  $e->getMessage() . PHP_EOL;
        }
    }

    public function wasModelRecentlyCreated(Bookings $booking): bool {
        return Carbon::now()->diffInSeconds($booking->created_at) < 5;
    }

    /**
     * This function is key to how FinTS works in times of PSD2 regulations. Most actions like wire transfers, getting
     * statements and even logging in can require a TAN, but won't always. Whether a TAN is required depends on the kind of
     * action, when it was last executed, other parameters like the amount (of a wire transfer) or time span (of a statement
     * request) and generally the security concept of the particular bank. The TAN requirements may or may not be consistent
     * with the TAN that the same bank requires for the same action in the web-based online banking interface. Also, banks
     * may change these requirements over time, so just because your particular bank does not need a TAN for login today
     * does not mean that it stays that way.
     *
     * @param BaseAction $action Some action that requires a TAN.
     * @throws CurlException
     * @throws ServerException
     */
    public function handleTan(BaseAction $action) {
        // Find out what sort of TAN we need, tell the user about it.
        $tanRequest = $action->getTanRequest();
        Log::info('The bank requested a TAN, asking: ' . $tanRequest->getChallenge());
        if ($tanRequest->getTanMediumName() !== null) {
            Log::info('Please use this device: ' . $tanRequest->getTanMediumName());
        }

        // Challenge Image for PhotoTan/ChipTan
        if ($tanRequest->getChallengeHhdUc()) {
            $challengeImage = new TanRequestChallengeImage(
                $tanRequest->getChallengeHhdUc()
            );
            Log::info("There is a challenge image.");
            // Save the challenge image somewhere
            // Alternative: HTML sample code
            Log::info('<img src="data:' . htmlspecialchars($challengeImage->getMimeType()) . ';base64,' . base64_encode($challengeImage->getData()) . '" />');
        }

        // Optional: Instead of printing the above to the console, you can relay the information (challenge and TAN medium)
        // to the user in any other way (through your REST API, a push notification, ...). If waiting for the TAN requires
        // you to interrupt this PHP session and the TAN will arrive in a fresh (HTTP/REST/...) request, you can do so:
        if ($optionallyPersistEverything = false) {
            $persistedAction = serialize($action);
            $persistedFints = $this->fints->persist();

            // These are two strings (watch out, they are NOT necessarily UTF-8 encoded), which you can store anywhere.
            // This example code stores them in a text file, but you might write them to your database (use a BLOB, not a
            // CHAR/TEXT field to allow for arbitrary encoding) or in some other storage (possibly base64-encoded to make it
            // ASCII).
            file_put_contents(storage_path('state.txt'), serialize([$persistedFints, $persistedAction]));
        }

        $tan = trim(readline('Please enter the TAN:'));

        // Optional: If the state was persisted above, we can restore it now (imagine this is a new PHP session).
        if ($optionallyPersistEverything) {
            $restoredState = file_get_contents(storage_path('state.txt'));
            [$persistedInstance, $persistedAction] = unserialize($restoredState);
            $this->createFints();
            $this->fints->loadPersistedInstance($persistedInstance);
            $action = unserialize($persistedAction);
        }

        Log::info("Submitting TAN: $tan");
        $this->fints->submitTan($action, $tan);
    }

    private function createFints(): void {
        // The configuration options up here are considered static wrt. the library's internal state and its requests.
        // That is, even if you persist the FinTs instance, you need to be able to reproduce all this information from some
        // application-specific storage (e.g. your database) in order to use the phpFinTS library.
        $options = new \Fhp\Options\FinTsOptions();
        $options->url = env('FHP_BANK_URL'); // HBCI / FinTS Url can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm (use the PIN/TAN URL)
        $options->bankCode = env('FHP_BANK_CODE'); // Your bank code / Bankleitzahl
        $options->productName = env('FHP_ONLINE_REGISTRATIONNO'); // The number you receive after registration / FinTS-Registrierungsnummer
        $options->productVersion = '1.0'; // Your own Software product version

        $pin = ($cmd = env('FHP_ONLINE_BANKING_PIN_CMD')) !== null ? trim(shell_exec($cmd)) : decrypt(env('FHP_ONLINE_BANKING_PIN'));

        $credentials = \Fhp\Options\Credentials::create(
            env('FHP_ONLINE_BANKING_USERNAME'),
            $pin // This is NOT the PIN of your bank card!
        );

        $this->fints = \Fhp\FinTs::new($options, $credentials);
        $this->fints->selectTanMode(env('FHP_TAN_MODE', 944), null);

        // Log in.
        $login = $this->fints->login();
        if ($login->needsTan()) {
            $this->handleTan($login);
        }
    }

    public function fireEvent(SEPAAccount $account, Transaction $transaction, float|int $saldo): void {
        \event(new NewEntry(
            $account,
            $transaction,
            $saldo
        ));
    }
}
