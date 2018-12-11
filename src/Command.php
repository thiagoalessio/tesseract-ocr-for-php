<?php namespace thiagoalessio\TesseractOCR;

class Command
{
	public $executable = 'tesseract';
	public $options = [];
	public $configFile;
	public $threadLimit;
	public $image;
	private $outputFile;

	public function __construct($image=null, $outputFile=null)
	{
		$this->image = $image;
		$this->outputFile = $outputFile ?: tempnam(sys_get_temp_dir(), 'ocr');
	}

	public function build() { return "$this"; }

	public function __toString()
	{
		$cmd = [];
		if ($this->threadLimit) $cmd[] = "OMP_THREAD_LIMIT={$this->threadLimit}";
		$cmd[] = self::escape($this->executable);
		$cmd[] = self::escape($this->image);
		$cmd[] = $this->outputFile;

		$version = $this->getTesseractVersion();

		foreach ($this->options as $option) {
			$cmd[] = is_callable($option) ? $option($version) : "$option";
		}
		if ($this->configFile) $cmd[] = $this->configFile;

		return join(' ', $cmd);
	}

	public function getOutputFile()
	{
		switch ($this->configFile) {
			case 'hocr': $ext = 'hocr'; break;
			case 'tsv': $ext = 'tsv'; break;
			case 'pdf': $ext = 'pdf'; break;
			default: $ext = 'txt';
		}
		return "{$this->outputFile}.{$ext}";
	}

	public function getTesseractVersion()
	{
		exec(self::escape($this->executable).' --version 2>&1', $output);
		return explode(' ', $output[0])[1];
	}

	public function getAvailableLanguages()
	{
		exec(self::escape($this->executable) . ' --list-langs 2>&1', $output);
		array_shift($output);
		sort($output);
		return $output;
	}

	public static function escape($str)
	{
		return '"'.str_replace('$', '\$', addcslashes($str, '\\"')).'"';
	}
}
