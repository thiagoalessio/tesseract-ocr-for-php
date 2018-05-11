<?php namespace thiagoalessio\TesseractOCR;

use thiagoalessio\TesseractOCR\Command;
use thiagoalessio\TesseractOCR\Option;

class TesseractOCR
{
	public $command;

	public function __construct($image, $command=null)
	{
		$this->command = $command ?: new Command($image);
	}

	public function run($threadLimit=0)
	{
		$cmd = "{$this->command}";
		if ($threadLimit > 0) {
			$cmd = "OMP_THREAD_LIMIT=" . $threadLimit . " " . $cmd;
		}
		exec($cmd, $output);
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
			$this->command->options[] = call_user_func_array($option, $args);
			return $this;
		}
		$this->command->options[] = Option::config($method, $args[0]);
		return $this;
	}

	private function isConfigFile($name)
	{
		return in_array($name, ['digits', 'hocr', 'pdf', 'quiet', 'tsv', 'txt']);
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
