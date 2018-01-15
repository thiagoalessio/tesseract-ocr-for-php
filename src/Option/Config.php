<?php namespace thiagoalessio\TesseractOCR\Option;

class Config
{
	public function __construct($var, $value)
	{
		$this->var = $var;
		$this->value = $value;
	}

	public function __toString()
	{
		$configPair = "{$this->toSnakeCase($this->var)}={$this->value}";
		return ' -c "'.addcslashes($configPair, '\\"').'"';
	}

	private function toSnakeCase($str)
	{
		return strtolower(preg_replace('/([A-Z])+/', '_$1', $str));
	}
}
