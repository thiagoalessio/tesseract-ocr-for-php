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

	public function __call($method, $args)
	{
		if ($this->isShortcut($method)) {
			$class = $this->getShortcutClassName($method);
			$this->command->options[] = $class::buildOption(...$args);
			return $this;
		}
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

	private function isShortcut($name)
	{
		return class_exists($this->getShortcutClassName($name));
	}

	private function getShortcutClassName($name)
	{
		return __NAMESPACE__.'\\Shortcut\\'.ucfirst($name);
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
