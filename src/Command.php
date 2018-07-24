<?php namespace thiagoalessio\TesseractOCR;

class Command
{
	public $executable = 'tesseract';
	public $options = [];
	public $configFile;
	public $threadLimit;
	public $image;
	private $outputFile;

	public function __construct($image=null)
	{
		$this->image = $image;
	}

	public function build() { return "$this"; }

	public function __toString()
	{
		$cmd = [];
		if ($this->threadLimit) $cmd[] = "OMP_THREAD_LIMIT={$this->threadLimit}";
		$cmd[] = self::escape($this->executable);
		$cmd[] = self::escape($this->image);
		$cmd[] = $this->getOutputFile();

		$version = $this->getTesseractVersion();

		foreach ($this->options as $option) {
			$cmd[] = is_callable($option) ? $option($version) : "$option";
		}
		if ($this->configFile) $cmd[] = $this->configFile;

		return join(' ', $cmd);
	}

	public function getOutputFile()
	{
		if (!$this->outputFile)
			$this->outputFile = tempnam(sys_get_temp_dir(), 'ocr');
		return $this->outputFile;
	}

	protected function getTesseractVersion()
	{
		exec(self::escape($this->executable).' --version 2>&1', $output);
		return explode(' ', $output[0])[1];
	}

	public static function escape($str)
	{
		return '"'.str_replace('$', '\$', addcslashes($str, '\\"')).'"';
	}
}
