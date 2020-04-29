<?php

namespace PKeidel\BankToLaravel\Commands;

use Carbon\Carbon;
use DateTime;
use Exception;
use Fhp\Action\GetSEPAAccounts;
use Fhp\Action\GetStatementOfAccount;
use Fhp\BaseAction;
use Fhp\CurlException;
use Fhp\FinTsNew;
use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Model\TanRequestChallengeImage;
use Fhp\Protocol\ServerException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PKeidel\BankToLaravel\Events\NewEntry;
use PKeidel\BankToLaravel\Models\Bookings;

class ImportEntries extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'bank:import';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Reads booking informations from an bank account and saves it to a local table in the database';

    private $fints;

    public function wasModelRecentlyCreated(Bookings $booking) {
        return Carbon::now()->diffInSeconds($booking->created_at) < 5;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
//        $this->info("checking bank account(s)...");

        $fints = $this->getFints();

        $from = new DateTime(env('FHP_BANK_START', '7 day ago'));
        $to   = new DateTime();
        try {
            $getSepaAccounts = GetSEPAAccounts::create();
            $fints->execute($getSepaAccounts);
            if ($getSepaAccounts->needsTan()) {
                $this->warn('TAN input required!');
                $this->handleTan($getSepaAccounts);
            }
            $accounts = $getSepaAccounts->getAccounts();
            foreach($accounts as $account) {
                if(empty(env('FHP_BANK_ACCOUNT')) || env('FHP_BANK_ACCOUNT') !== $account->getAccountNumber()) {
//                    $this->info("Skipping account: " . $account->getAccountNumber());
                    continue;
                }

//                $this->info("Importing account: " . $account->getAccountNumber());

                $getStatement = GetStatementOfAccount::create($account, $from, $to);
                $fints->execute($getStatement);
//                $this->info("  needsTan(): " . ($getStatement->needsTan() ? 'y' : 'n'));
                if ($getStatement->needsTan()) {
                    $this->warn("TAN input required!");
                    $this->handleTan($getStatement);
                }

                $soa = $getStatement->getStatement();
                foreach ($soa->getStatements() as $statement) {
                    $saldo = $statement->getCreditDebit() == Statement::CD_DEBIT ? 0 - $statement->getStartBalance() : $statement->getStartBalance();
//                    $this->info($statement->getDate()->format('Y-m-d') . ": Start Saldo: $saldo");
                    foreach ($statement->getTransactions() as $transaction) {
//                        $this->info("  - importing: {$transaction->getBookingDate()->format('Y-m-d')} {$transaction->getBookingText()} {$transaction->getCreditDebit()} {$transaction->getAmount()}");

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
                            $this->info("    => this one was new, so fire an NewEntry event");
                            event(new NewEntry($booking->toArray(), $saldo));
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::error($e);
//            $this->warn("ERROR " . get_class($e) . " ({$e->getFile()}:{$e->getLine()}): " . $e->getMessage());
//            $this->info($e);
        }
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
     * The TAN can be provided it many different ways. Each application that uses the phpFinTS library has to implement
     * its own way of asking users for a TAN, depending on its user interfaces. The implementation does not have to be in a
     * function like this, it can be inlined with the calling code, or live elsewhere. The TAN can be obtained while the
     * same PHP script is still running (i.e. handleTan() is a blocking function that only returns once the TAN is known),
     * but it is also possible to interrupt the PHP execution entirely while asking for the TAN.
     *
     * @param BaseAction $action Some action that requires a TAN.
     * @throws CurlException
     * @throws ServerException
     */
    public function handleTan(BaseAction $action) {
        // Find out what sort of TAN we need, tell the user about it.
        $tanRequest = $action->getTanRequest();
        $this->info('The bank requested a TAN, asking: ' . $tanRequest->getChallenge());
        if ($tanRequest->getTanMediumName() !== null) {
            $this->info('Please use this device: ' . $tanRequest->getTanMediumName());
        }

        // Challenge Image for PhotoTan/ChipTan
        if ($tanRequest->getChallengeHhdUc()) {
            $challengeImage = new TanRequestChallengeImage(
                $tanRequest->getChallengeHhdUc()
            );
            $this->info("There is a challenge image.");
            // Save the challenge image somewhere
            // Alternative: HTML sample code
            echo '<img src="data:' . htmlspecialchars($challengeImage->getMimeType()) . ';base64,' . base64_encode($challengeImage->getData()) . '" />' . PHP_EOL;
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

        $tan = trim($this->ask('Please enter the TAN:'));

        // Optional: If the state was persisted above, we can restore it now (imagine this is a new PHP session).
        if ($optionallyPersistEverything) {
            $restoredState = file_get_contents(storage_path('state.txt'));
            list($persistedInstance, $persistedAction) = unserialize($restoredState);
            $this->fints = $this->getFints();
            $this->fints->loadPersistedInstance($persistedInstance);
            $action = unserialize($persistedAction);
        }

        $this->info("Submitting TAN: $tan");
        $this->fints->submitTan($action, $tan);
    }

    private function getFints() {
        $this->fints = new FinTsNew(
            env('FHP_BANK_URL'),
            env('FHP_BANK_CODE'),
            env('FHP_ONLINE_BANKING_USERNAME'),
            decrypt(env('FHP_ONLINE_BANKING_PIN')),
            env('FHP_ONLINE_REGISTRATIONNO'),
            '1.0'
        );
        $this->fints->selectTanMode(942);

        // Log in.
        $login = $this->fints->login();
        if ($login->needsTan()) {
            $this->handleTan($login);
        }

        // Usage:
        // $fints = require_once 'login.php';
        return $this->fints;
    }

}
