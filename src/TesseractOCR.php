<?php namespace thiagoalessio\TesseractOCR;

use thiagoalessio\TesseractOCR\Command;
use thiagoalessio\TesseractOCR\Option;
use thiagoalessio\TesseractOCR\FriendlyErrors;

class TesseractOCR
{
	public $command;
	private $outputFile = null;

	public function __construct($image=null, $command=null)
	{
		$this->command = $command ?: new Command;
		$this->image("$image");
	}

	public function run()
	{
		if ($this->outputFile !== null) {
			FriendlyErrors::checkWritePermissions($this->outputFile);
			$this->command->useFileAsOutput = true;
		}

		FriendlyErrors::checkTesseractPresence($this->command->executable);
		if ($this->command->useFileAsInput)
			FriendlyErrors::checkImagePath($this->command->image);

		$process = new Process("{$this->command}");

		if (! $this->command->useFileAsInput)
		{
			$process->write($this->command->image, $this->command->imageSize);
			$process->closeStdin();
		}
		$output = $process->wait();

		FriendlyErrors::checkCommandExecution($this->command, $output["out"], $output["err"]);

		if ($this->command->useFileAsOutput)
		{
			$text = file_get_contents($this->command->getOutputFile());

			if ($this->outputFile !== null) {
				rename($this->command->getOutputFile(), $this->outputFile);
			}

			$this->cleanTempFiles();
		}
		else
			$text = $output["out"];
		return trim($text, " \t\n\r\0\x0A\x0B\x0C");
	}

	public function imageData($image, $size)
	{
		FriendlyErrors::checkTesseractVersion("3.03-rc1", "Reading image data from stdin", $this->command);
		$this->command->useFileAsInput = false;
		$this->command->image = $image;
		$this->command->imageSize = $size;
		return $this;
	}

	public function withoutTempFiles()
	{
		FriendlyErrors::checkTesseractVersion("3.03-rc1", "Writing to stdout (without using temp files)", $this->command);
		$this->command->useFileAsOutput = false;
		return $this;
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

	public function setOutputFile($path) {
		$this->outputFile = $path;
	}

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

	private function cleanTempFiles()
	{
		if (file_exists($this->command->getOutputFile(false)))
		{   
			unlink($this->command->getOutputFile(false));
		}
		if (file_exists($this->command->getOutputFile(true)))
		{
			unlink($this->command->getOutputFile(true));
		}
	}
}
