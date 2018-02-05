<?php namespace thiagoalessio\TesseractOCR;

use thiagoalessio\TesseractOCR\Format\Text;

class TesseractOCR
{
	private $image;
	private $executable = 'tesseract';
	private $options = [];
	private $format;

	public function __construct($image)
	{
		$this->image = $image;
		$this->format = new Text();
	}

	public function run()
	{
		exec($this->buildCommand(), $output);
		return trim(join(PHP_EOL, $output));
	}

	public function buildCommand()
	{
		return Command::build($this->image, $this->executable, $this->options, $this->format);
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
		if ($this->isFormat($method)) {
				$class = $this->getFormatClassName($method);
				$this->format = new $class(...$args);
				return $this;
		}
		$this->options[] = new Option\Config($method, $args[0]);
		return $this;
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

	private function isFormat($name)
	{
		return class_exists($this->getFormatClassName($name));
	}

	private function getFormatClassName($name)
	{
		return __NAMESPACE__.'\\Format\\'.ucfirst($name);
	}
}
