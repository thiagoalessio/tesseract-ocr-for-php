<?php namespace thiagoalessio\TesseractOCR;

class ImageNotFoundException extends \Exception {}
class TesseractNotFoundException extends \Exception {}
class UnsuccessfulCommandException extends \Exception {}
class FeatureNotAvailableException extends \Exception {}

class FriendlyErrors
{
	public static function checkImagePath($image)
	{
		if (file_exists($image)) return;

		$currentDir = __DIR__;
		$msg = array();
		$msg[] = "Error! The image \"$image\" was not found.";
		$msg[] = '';
		$msg[] = "The current __DIR__ is $currentDir";
		$msg = join(PHP_EOL, $msg);

		throw new ImageNotFoundException($msg);
	}

	public static function checkTesseractPresence($executable)
	{
		if (file_exists($executable)) return;

		$cmd = stripos(PHP_OS, 'win') === 0
			? 'where.exe '.Command::escape($executable).' > NUL 2>&1'
			: 'type '.Command::escape($executable).' > /dev/null 2>&1';
		system($cmd, $exitCode);

		if ($exitCode == 0) return;

		$currentPath = getenv('PATH');
		$msg = array();
		$msg[] = "Error! The command \"$executable\" was not found.";
		$msg[] = '';
		$msg[] = 'Make sure you have Tesseract OCR installed on your system:';
		$msg[] = 'https://github.com/tesseract-ocr/tesseract';
		$msg[] = '';
		$msg[] = "The current \$PATH is $currentPath";
		$msg = join(PHP_EOL, $msg);

		throw new TesseractNotFoundException($msg);
	}

	public static function checkCommandExecution($command, $stdout, $stderr)
	{
		$file = $command->getOutputFile();

		if (($command->useFileAsOutput && file_exists($file) && filesize($file) > 0) ||
			(!$command->useFileAsOutput && $stdout)) return;

		$msg = array();
		$msg[] = 'Error! The command did not produce any output.';
		$msg[] = '';
		$msg[] = 'Generated command:';
		$msg[] = "$command";
		$msg[] = '';
		$msg[] = 'Returned message:';
		$arrayStderr = explode(PHP_EOL, $stderr);
		array_pop($arrayStderr);
		$msg = array_merge($msg, $arrayStderr);
		$msg = join(PHP_EOL, $msg);

		throw new UnsuccessfulCommandException($msg);
	}

	public static function checkProcessCreation($processHandle, $command)
	{
		if ($processHandle !== FALSE) return;

		$msg = array();
		$msg[] = 'Error! The command could not be launched.';
		$msg[] = '';
		$msg[] = 'Generated command:';
		$msg[] = "$command";
		$msg = join(PHP_EOL, $msg);

		throw new UnsuccessfulCommandException($msg);
	}

	public static function checkTesseractVersion($expected, $action, $command)
	{
		$actual = $command->getTesseractVersion();
		if (version_compare($actual, $expected, ">=")) return;

		$msg = array();
		$msg[] = "Error! $action is not available this tesseract version";
		$msg[] = "Required version is $expected, actual version is $actual";
		$msg[] = '';
		$msg[] = 'Generated command:';
		$msg[] = "$command";
		$msg = join(PHP_EOL, $msg);

		throw new FeatureNotAvailableException($msg);
	}
}
