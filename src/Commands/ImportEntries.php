<?php

namespace PKeidel\BankToLaravel\Commands;

use Illuminate\Console\Command;
use Fhp\FinTs;
use PKeidel\BankToLaravel\Events\Error;
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

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$accounts = null;
		$fints = new FinTs(
			env('FHP_BANK_URL'),
			intval(env('FHP_BANK_PORT')),
			env('FHP_BANK_CODE'),
			env('FHP_ONLINE_BANKING_USERNAME'),
			env('FHP_ONLINE_BANKING_PIN')
		);

		try {
			$accounts = $fints->getSEPAAccounts();
		} catch(\Exception $e) {
			event(new Error($e));
			exit;
		}

		/** @var \Fhp\Model\SEPAAccount $oneAccount */
		$oneAccount = $accounts[0];

		$from = new \DateTime(env('FHP_BANK_START', '1 days ago'));
		$to   = new \DateTime();
		$soa = $fints->getStatementOfAccount($oneAccount, $from, $to);

		foreach ($soa->getStatements() as $statement) {
			// $saldo = $statement->getCreditDebit() == Statement::CD_DEBIT ? 0 - $statement->getStartBalance() : $statement->getStartBalance();
			// $this->alert($statement->getDate()->format('Y-m-d') . ": Start Saldo: $saldo");
			foreach ($statement->getTransactions() as $transaction) {
				// Check if entry already exists in the database

				/** @var \Fhp\Model\StatementOfAccount\Transaction $transaction */
				$i  = $oneAccount->getIban();
				$d1 = $transaction->getBookingDate()->format('U');
				$d2 = $transaction->getValutaDate()->format('U');
				$a  = $transaction->getAmount();
				$c  = $transaction->getCreditDebit();
				$b  = $transaction->getBookingText();
				$d  = $transaction->getDescription1();
				$md5 = md5("$i-$d1-$d2-$a-$c-$b-$d");

				$booking = Bookings::firstOrNew(['search' => $md5]);
				if(!$booking->exists) {
					$booking->ref_iban = $oneAccount->getIban();
					$booking->bookingdate = $transaction->getBookingDate();
					$booking->valutadate = $transaction->getValutaDate();
					$booking->amount = $transaction->getAmount();
					$booking->creditdebit = $transaction->getCreditDebit();
					$booking->bookingtext = $transaction->getBookingText();
					$booking->description1 = $transaction->getDescription1();
					$booking->structureddescription = $transaction->getStructuredDescription();
					$booking->bankcode = $transaction->getBankCode();
					$booking->accountnumber = $transaction->getAccountNumber();
					$booking->name = $transaction->getName();
					// echo "HERE THE NEW ENTRY WILL BE SAVED TO DB normally...\n";
					$booking->save();
					event(new NewEntry($booking->toArray()));
				}
			}
		}
	}
}
