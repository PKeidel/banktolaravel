<?php

namespace PKeidel\BankToLaravel\Events;

class NewEntry {

	public $data = [];
	public $saldo = null;

	public function __construct($data, $saldo) {
		$this->data = $data;
		$this->saldo = $saldo;
	}

}