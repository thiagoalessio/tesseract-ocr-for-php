<?php namespace thiagoalessio\TesseractOCR;

use thiagoalessio\TesseractOCR\Option;

class TesseractOCR
{
	private $command;

	public function __construct($image, $command=null)
	{
		$class = $this->getCommandClassName($command);
		$this->command = new $class($image);
	}

	public function run()
	{
		exec($this->buildCommand(), $output);
		return trim(join(PHP_EOL, $output));
	}

	public function buildCommand()
	{
		return $this->command->build();
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

	public function psm($psm)
	{
		$this->command->options[] = Option::psm($psm);
		return $this;
	}

	public function oem($oem)
	{
		$this->command->options[] = Option::oem($oem);
		return $this;
	}

	public function userWords($path)
	{
		$this->command->options[] = Option::userWords($path);
		return $this;
	}

	public function userPatterns($path)
	{
		$this->command->options[] = Option::userPatterns($path);
		return $this;
	}

	public function tessdataDir($path)
	{
		$this->command->options[] = Option::tessdataDir($path);
		return $this;
	}

	public function lang()
	{
		$this->command->options[] = Option::lang(func_get_args());
		return $this;
	}

	public function __call($method, $args)
	{
		if ($this->isShortcut($method)) {
			$class = $this->getShortcutClassName($method);
			$this->command->options[] = $class::buildOption(...$args);
			return $this;
		}
		if ($this->isOption($method)) {
			$class = $this->getOptionClassName($method);
			$this->command->options[] = new $class(...$args);
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
		return class_exists($this->getOptionClassName($name));
	}

	private function getOptionClassName($name)
	{
		return __NAMESPACE__.'\\Option\\'.ucfirst($name);
	}
}
