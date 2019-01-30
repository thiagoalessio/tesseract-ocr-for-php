<?php namespace thiagoalessio\TesseractOCR;

use thiagoalessio\TesseractOCR\Command;
use thiagoalessio\TesseractOCR\Option;
use thiagoalessio\TesseractOCR\FriendlyErrors;

class TesseractOCR
{
	public $command;

	public function __construct($image=null, $command=null)
	{
		$this->command = $command ?: new Command;
		$this->image("$image");
	}

	public function run()
	{
		FriendlyErrors::checkTesseractPresence($this->command->executable);
		FriendlyErrors::checkImagePath($this->command->image);

		exec("{$this->command} 2>&1", $stdout);

		FriendlyErrors::checkCommandExecution($this->command, $stdout);

		$text = file_get_contents($this->command->getOutputFile());
		return trim($text, " \t\n\r\0\x0A\x0B\x0C");
	}

	public function image($image)
	{
		$this->command->image = $image;
		return $this;
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

	public function tempDir($tempDir)
	{
		$this->command->tempDir = $tempDir;
		return $this;
	}

	public function threadLimit($limit)
	{
		$this->command->threadLimit = $limit;
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

	public function version()
	{
		return $this->command->getTesseractVersion();
	}

	public function availableLanguages()
	{
		return $this->command->getAvailableLanguages();
	}

	public function __call($method, $args)
	{
		if ($this->isConfigFile($method)) return $this->configFile($method);
		if ($this->isOption($method)) {
			$option = $this->getOptionClassName().'::'.$method;
			$this->command->options[] = call_user_func_array($option, $args);
			return $this;
		}
		$arg = empty($args) ? null : $args[0];
		$this->command->options[] = Option::config($method, $arg);
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
