<?php

namespace PKeidel\BankToLaravel\Events;

use Fhp\Segment\SAL\HISAL;

class NewBalance {
	public function __construct(
        public readonly HISAL $hisal
    ) {}
}
