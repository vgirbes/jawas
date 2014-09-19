<?php
class Invoker {
	private $commands, $selector, $current;
	public function __construct() {
		$this->current =-1;
	}

	public function Select($command) {
		$command->Execute();
		$this->current++;
		$this->commands[$this->current] = $command;
	}
}