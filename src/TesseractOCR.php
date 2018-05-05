<?php namespace thiagoalessio\TesseractOCR;

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
		$this->command->options[] = new Option\Config($method, $args[0]);
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
