<?php namespace thiagoalessio\TesseractOCR;

class ImageNotFoundException extends \Exception {}
class TesseractNotFoundException extends \Exception {}
class UnsuccessfulCommandException extends \Exception {}

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

	public static function checkCommandExecution($command, $stdout)
	{
		$file = $command->getOutputFile();

		if (file_exists($file) && filesize($file) > 0) return;

		$msg = array();
		$msg[] = 'Error! The command did not produce any output.';
		$msg[] = '';
		$msg[] = 'Generated command:';
		$msg[] = "$command";
		$msg[] = '';
		$msg[] = 'Returned message:';
		$msg = array_merge($msg, $stdout);
		$msg = join(PHP_EOL, $msg);

		throw new UnsuccessfulCommandException($msg);
	}
}
