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

	public function getTesseractVersion()
	{
		exec(self::escape($this->executable).' --version 2>&1', $output);
		return explode(' ', $output[0])[1];
	}

	/**
	 * Finds what language files Tesseract binary is installed with.
	 *
	 * @return array Codes loosely based on ISO 3166-1 alpha 3 codes.
	 *               Language localizations are defined by an appended _ followed by the localization name.
	 *               OSD language stands for 'Orientation and script detection'
	 */

	public function getTesseractLangs()
	{
		exec(self::escape($this->executable) . ' --list-langs 2>&1', $output);

		foreach ($output as $key => $lang) {
			if (! preg_match('/^[a-z]{3}(_[a-z_]+)?$/m', $lang)) {
				unset ($output[$key]);
			}
		}
		return $output;
	}

	public static function escape($str)
	{
		return '"'.str_replace('$', '\$', addcslashes($str, '\\"')).'"';
	}
}
