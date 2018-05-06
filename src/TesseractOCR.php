<?php namespace thiagoalessio\TesseractOCR;

use thiagoalessio\TesseractOCR\Option;

class TesseractOCR
{
	public $command;

	public function __construct($image, $command=null)
	{
		$class = $this->getCommandClassName($command);
		$this->command = new $class($image);
	}

	public function run()
	{
		exec("{$this->command}", $output);
		return trim(join(PHP_EOL, $output));
	}

	public function executable($executable)
	{
		$this->command->executable = $executable;
		return $this;
	}

	public function configFile($configFile)
	{
		$this->command->configFile = $configFile;
		return $this;
	}

	// @deprecated
	public function format($fmt) { return $this->configFile($fmt); }

	public function whitelist()
	{
		$concat = function ($arg) { return is_array($arg) ? join('', $arg) : $arg; };
		$whitelist = join('', array_map($concat, func_get_args()));
		$this->command->options[] = Option::config('tessedit_char_whitelist', $whitelist);
		return $this;
	}

	public function __call($method, $args)
	{
		if ($this->isConfigFile($method)) return $this->configFile($method);
		if ($this->isOption($method)) {
			$option = $this->getOptionClassName().'::'.$method;
			$this->command->options[] = call_user_func($option, $args);
			return $this;
		}
		$this->command->options[] = Option::config($method, $args[0]);
		return $this;
	}

	private function getCommandClassName($command)
	{
		return $command ?: __NAMESPACE__.'\\Command';
	}

	private function isConfigFile($name)
	{
		return in_array($name, ['hocr', 'tsv']);
	}

	private function isOption($name)
	{
		return in_array($name, get_class_methods($this->getOptionClassName()));
	}

	private function getOptionClassName()
	{
		return __NAMESPACE__.'\\Option';
	}
}
