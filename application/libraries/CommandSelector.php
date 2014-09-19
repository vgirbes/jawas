<?php
class CommandSelector extends Command {
	private $selector, $provider, $CI, $country_id, $user_name;
	public $items;
	public function __construct ($selector, $provider, $CI, $country_id, $user_name) {
		$this->provider = $provider;
		$this->selector = $selector;
		$this->CI = $CI;
		$this->country_id = $country_id;
		$this->user_name = $user_name;	
	}

	public function Execute() {
		$this->items = $this->selector->Action($this->provider, $this->CI, $this->country_id, $this->user_name);
	}
}