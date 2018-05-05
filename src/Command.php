<?php namespace thiagoalessio\TesseractOCR;

class Command
{
	public $executable = 'tesseract';
	private $image;

	public function __construct($image)
	{
		$this->image = $image;
	}

	public function build($options=[])
	{
		$cmd = [];
		$cmd[] = self::escape($this->executable);
		$cmd[] = self::escape($this->image);
		$cmd[] = 'stdout';

		if (!empty($options)) $cmd[] = join(' ', $options);
		if (static::isVersion303($this->executable)) $cmd[] = 'quiet';

		return join(' ', $cmd);
	}

	protected static function isVersion303($executable)
	{
		$version = self::getTesseractVersion($executable);
		return version_compare($version, '3.03', '>=')
			&& version_compare($version, '3.04', '<');
	}

	private static function getTesseractVersion($executable)
	{
		exec(self::escape($executable).' --version 2>&1', $output);
		return explode(' ', $output[0])[1];
	}

	private static function escape($str)
	{
		return '"'.addcslashes($str, '\\"').'"';
	}
}
