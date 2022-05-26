<?php

namespace PKeidel\BankToLaravel\Events;

use Fhp\Model\SEPAAccount;
use Fhp\Model\StatementOfAccount\Transaction;

class NewEntry {
	public function __construct(
        public readonly SEPAAccount $account,
        public readonly Transaction $transaction,
        public readonly float $saldo
    ) {}
}