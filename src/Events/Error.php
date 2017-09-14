<?php

namespace PKeidel\BankToLaravel\Events;

class Error {

	public $exception = [];

	public function __construct($exception) {
		$this->exception = $exception;
	}

}