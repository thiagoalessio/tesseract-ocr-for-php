<?php namespace thiagoalessio\TesseractOCR;

class TesseractOCR
{
	private $image;
	private $command;
	private $executable = 'tesseract';
	private $options = [];

	public function __construct($image, $command=null)
	{
		$this->image = $image;
		$this->command = $command;
	}

	public function run()
	{
		exec($this->buildCommand(), $output);
		return trim(join(PHP_EOL, $output));
	}

	public function buildCommand()
	{
		$class = $this->getCommandClassName();
		return $class::build($this->image, $this->executable, $this->options);
	}

	public function executable($executable)
	{
		$this->executable = $executable;
		return $this;
	}

	public function __call($method, $args)
	{
		if ($this->isShortcut($method)) {
			$class = $this->getShortcutClassName($method);
			$this->options[] = $class::buildOption(...$args);
			return $this;
		}
		if ($this->isOption($method)) {
			$class = $this->getOptionClassName($method);
			$this->options[] = new $class(...$args);
			return $this;
		}
		$this->options[] = new Option\Config($method, $args[0]);
		return $this;
	}

	private function getCommandClassName()
	{
		return $this->command ?: __NAMESPACE__.'\\Command';
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
