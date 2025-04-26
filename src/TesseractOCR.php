<?php namespace thiagoalessio\TesseractOCR;

use thiagoalessio\TesseractOCR\Command;
use thiagoalessio\TesseractOCR\Option;
use thiagoalessio\TesseractOCR\FriendlyErrors;

class TesseractOCR
{
	public $command;
	private $outputFile = null;

	public function __construct($image=null, $command=null)
	{
		$this->command = $command ?: new Command;
		$this->image("$image");
	}

	public function run($timeout = 0)
	{
		// Get Tesseract version (cached by Command class)
		$tesseractVersion = $this->command->getTesseractVersion();
		$isVersion303OrNewer = version_compare($tesseractVersion, '3.03', '>=');

		try {
			// Default to stdout for Tesseract 3.03+ if no output file is specified
			// This check needs to happen *before* checkWritePermissions, but *after*
			// the user has had a chance to call setOutputFile or withoutTempFiles.
			// withoutTempFiles() sets useFileAsOutput to false directly.
			// setOutputFile() sets $this->outputFile to a non-null value.
			if ($isVersion303OrNewer && $this->outputFile === null && $this->command->useFileAsOutput === true) {
				// User did not call setOutputFile() and did not call withoutTempFiles()
				// So, we can default to using stdout for better performance.
				$this->command->useFileAsOutput = false;
			}

			// If we are writing to a file (explicitly or due to old Tesseract version), check permissions.
			if ($this->command->useFileAsOutput && $this->outputFile !== null) {
				FriendlyErrors::checkWritePermissions($this->outputFile);
				// Ensure useFileAsOutput is true if outputFile was set, overriding potential default.
				// This scenario is unlikely now due to the logic order but kept for robustness.
				$this->command->useFileAsOutput = true;
			} elseif ($this->command->useFileAsOutput && $this->outputFile === null) {
				// If using file output but no specific file was given, TesseractOCR creates
				// a temp file. No specific permission check needed here beforehand.
				// Tesseract/Command class handles temp file creation.
			}


			FriendlyErrors::checkTesseractPresence($this->command->executable);
			// Input validation depends on whether we are using a file or stdin
			if ($this->command->useFileAsInput) {
				FriendlyErrors::checkImagePath($this->command->image);
			} else {
				// If using stdin, ensure the Tesseract version supports it.
				// The imageData() method already performs this check, but double-checking here
				// adds robustness in case the command object was manipulated differently.
				if (!$isVersion303OrNewer) {
					throw new FeatureNotAvailableException("Reading image data from stdin", "3.03", $tesseractVersion);
				}
			}


			$process = new Process("{$this->command}");

			// Write image data to stdin if not using a file
			if (!$this->command->useFileAsInput) {
				$process->write($this->command->image, $this->command->imageSize);
				$process->closeStdin();
			}
			$output = $process->wait($timeout);

			FriendlyErrors::checkCommandExecution($this->command, $output["out"], $output["err"]);
		}
		catch (TesseractOcrException $e) {
			// Clean up temporary files only if we were configured to use them
			if ($this->command->useFileAsOutput) $this->cleanTempFiles();
			throw $e;
		}

		// Process the output
		if ($this->command->useFileAsOutput) {
			// Read from the generated (temporary or specified) file
			$outputFilePath = $this->command->getOutputFile();
			$text = file_get_contents($outputFilePath);

			// If a specific output file was requested, rename the temp file to it
			if ($this->outputFile !== null && $outputFilePath !== $this->outputFile) {
				rename($outputFilePath, $this->outputFile);
			}

			// Clean up temporary files (if any were used)
			$this->cleanTempFiles();
		} else {
			// Read directly from stdout
			$text = $output["out"];
		}

		return trim($text, " \t\n\r\0\x0A\x0B\x0C");
	}

	public function imageData($image, $size)
	{
		FriendlyErrors::checkTesseractVersion("3.03-rc1", "Reading image data from stdin", $this->command);
		$this->command->useFileAsInput = false;
		$this->command->image = $image;
		$this->command->imageSize = $size;
		return $this;
	}

	public function withoutTempFiles()
	{
		FriendlyErrors::checkTesseractVersion("3.03-rc1", "Writing to stdout (without using temp files)", $this->command);
		$this->command->useFileAsOutput = false;
		return $this;
	}

	public function image($image)
	{
		$this->command->image = $image;
		return $this;
	}

	public function executable($executable)
	{
		FriendlyErrors::checkTesseractPresence($executable);
		$this->command->executable = $executable;
		return $this;
	}

	public function configFile($configFile)
	{
		$this->command->configFile = $configFile;
		return $this;
	}

	public function tempDir($tempDir)
	{
		$this->command->tempDir = $tempDir;
		return $this;
	}

	public function threadLimit($limit)
	{
		$this->command->threadLimit = $limit;
		return $this;
	}

	// @deprecated
	public function format($fmt) { return $this->configFile($fmt); }

	public function setOutputFile($path) {
		$this->outputFile = $path;
		return $this;
	}

	public function allowlist()
	{
		$concat = function ($arg) { return is_array($arg) ? join('', $arg) : $arg; };
		$allowlist = join('', array_map($concat, func_get_args()));
		$this->command->options[] = Option::config('tessedit_char_whitelist', $allowlist);
		return $this;
	}

	public function whitelist()
	{
		$warningMsg = 'Notice: whitelist is deprecated, use allowlist instead.';
		trigger_error($warningMsg, E_USER_NOTICE);

		$concat = function ($arg) { return is_array($arg) ? join('', $arg) : $arg; };
		$allowlist = join('', array_map($concat, func_get_args()));
		return $this->allowlist($allowlist);
	}

	public function version()
	{
		return $this->command->getTesseractVersion();
	}

	public function availableLanguages()
	{
		return $this->command->getAvailableLanguages();
	}

	public function __call($method, $args)
	{
		if ($this->isConfigFile($method)) return $this->configFile($method);
		if ($this->isOption($method)) {
			$option = $this->getOptionClassName().'::'.$method;
			$this->command->options[] = call_user_func_array($option, $args);
			return $this;
		}
		$arg = empty($args) ? null : $args[0];
		$this->command->options[] = Option::config($method, $arg);
		return $this;
	}

	private function isConfigFile($name)
	{
		return in_array($name, array('digits', 'hocr', 'pdf', 'quiet', 'tsv', 'txt'));
	}

	private function isOption($name)
	{
		return in_array($name, get_class_methods($this->getOptionClassName()));
	}

	private function getOptionClassName()
	{
		return __NAMESPACE__.'\\Option';
	}

	private function cleanTempFiles()
	{
		if (file_exists($this->command->getOutputFile(false))) {
			unlink($this->command->getOutputFile(false));
		}
		if (file_exists($this->command->getOutputFile(true))) {
			unlink($this->command->getOutputFile(true));
		}
	}
}
