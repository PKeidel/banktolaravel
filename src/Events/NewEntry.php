<?php

namespace PKeidel\BankToLaravel\Events;

class NewEntry {

	public $data = [];

	public function __construct($data) {
		$this->data = $data;
	}

}