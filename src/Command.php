<?php namespace thiagoalessio\TesseractOCR;

class Command
{
	public $executable = 'tesseract';
	public $options = [];
	public $configFile;
	public $threadLimit;
	private $image;
	private $outputFile;

	public function __construct($image)
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
		if ($this->isVersion303()) $this->configFile = 'quiet';
		if ($this->configFile) $cmd[] = $this->configFile;

		return join(' ', $cmd);
	}

	public function getOutputFile()
	{
		if (!$this->outputFile)
			$this->outputFile = tempnam(sys_get_temp_dir(), 'ocr');
		return $this->outputFile;
	}

	private function isVersion303()
	{
		$version = $this->getTesseractVersion();
		return version_compare($version, '3.03', '>=')
			&& version_compare($version, '3.04', '<');
	}

	protected function getTesseractVersion()
	{
		exec(self::escape($this->executable).' --version 2>&1', $output);
		return explode(' ', $output[0])[1];
	}

	private static function escape($str)
	{
		return '"'.addcslashes($str, '\\"').'"';
	}
}
