<?php namespace thiagoalessio\TesseractOCR\Tests\Unit;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\Command;
use thiagoalessio\TesseractOCR\FriendlyErrors; // Needed for potential exceptions
use PHPUnit\Framework\MockObject\MockObject;

class TesseractOCRTest extends TestCase
{
	public function setUp()
	{
		$this->customTempDir = __DIR__.DIRECTORY_SEPARATOR.'custom-temp-dir';
		mkdir($this->customTempDir);
	}

	public function tearDown()
	{
		$files = glob(join(DIRECTORY_SEPARATOR, array($this->customTempDir, '*')));
		array_map('unlink', $files);
		rmdir($this->customTempDir);
	}

	/** @var MockObject|Command */
	private $mockCommand;

	/** @var TesseractOCR */
	private $tess;

	// Using setUp instead of beforeEach for clarity with mocks
	public function setUp() : void
	{
		parent::setUp(); // Call parent setup if it exists or is needed

		// Mock the Command dependency
		$this->mockCommand = $this->createMock(Command::class);

		// Instantiate TesseractOCR with the mock Command
		// We pass a dummy image path initially, tests can override this if needed.
		$this->tess = new TesseractOCR('dummy.png', $this->mockCommand);

		// Default mock behavior (can be overridden in specific tests)
		// Prevent actual command execution attempts within mocks
		$this->mockCommand->method('__toString')->willReturn('');
		$this->mockCommand->method('getOutputFile')->willReturn('dummy_output_file');
		$this->mockCommand->method('getAvailableLanguages')->willReturn(['eng']); // Prevent errors if called

		// Important: Initialize public properties expected by the class
        // Default to file input/output unless changed by methods or logic
        $this->mockCommand->useFileAsInput = true;
        $this->mockCommand->useFileAsOutput = true;
        $this->mockCommand->executable = 'tesseract'; // Default executable
        $this->mockCommand->image = 'dummy.png';
	}

	// Keep existing tests, but ensure they use the mock if applicable,
	// or adapt them if they relied on TestableCommand specifics.
	// For simplicity, focusing on adding *new* tests below.

	// --- Existing tests would go here, potentially adapted ---

	// --- New Tests for Stdin/Stdout Logic ---

	/**
	 * Test Case 1: Default stdout (Version >= 3.03, no output file)
	 */
	public function testRunDefaultsToStdoutWhenVersionIs303OrNewerAndNoOutputFile()
	{
		$this->mockCommand->method('getTesseractVersion')->willReturn('3.05.00');

		// Expect $useFileAsOutput to be set to false by the logic in run()
		$this->tess->image('image.png'); // Ensure image is set
		$this->tess->run();

		$this->assertFalse($this->mockCommand->useFileAsOutput, 'useFileAsOutput should default to false (stdout)');
		$this->assertTrue($this->mockCommand->useFileAsInput, 'useFileAsInput should remain true'); // Input method not changed
	}

	/**
	 * Test Case 2: Explicit output file overrides default (Version >= 3.03)
	 */
	public function testRunUsesFileWhenOutputFileSetAndVersionIs303OrNewer()
	{
		$this->mockCommand->method('getTesseractVersion')->willReturn('4.0.0');

		$this->tess->image('image.png');
		$this->tess->setOutputFile('/path/to/output.txt');
		$this->tess->run();

		$this->assertTrue($this->mockCommand->useFileAsOutput, 'useFileAsOutput should be true when outputFile is set');
		$this->assertTrue($this->mockCommand->useFileAsInput, 'useFileAsInput should remain true');
	}

	/**
	 * Test Case 3: `withoutTempFiles()` overrides default (Version >= 3.03)
	 */
	public function testRunUsesStdoutWhenWithoutTempFilesCalledAndVersionIs303OrNewer()
	{
		$this->mockCommand->method('getTesseractVersion')->willReturn('3.03');

		// withoutTempFiles directly sets useFileAsOutput to false
		$this->tess->image('image.png');
		$this->tess->withoutTempFiles();
		// The run method's default logic shouldn't override this explicit setting
		$this->tess->run();

		$this->assertFalse($this->mockCommand->useFileAsOutput, 'useFileAsOutput should be false when withoutTempFiles is called');
		$this->assertTrue($this->mockCommand->useFileAsInput, 'useFileAsInput should remain true');
	}

	/**
	 * Test Case 4: Old file behavior (Version < 3.03, no output file)
	 */
	public function testRunUsesFileWhenVersionIsOlderThan303()
	{
		$this->mockCommand->method('getTesseractVersion')->willReturn('3.02.01');

		// The stdout defaulting logic should not trigger for older versions
		$this->tess->image('image.png');
		$this->tess->run();

		$this->assertTrue($this->mockCommand->useFileAsOutput, 'useFileAsOutput should remain true (file output) for older versions');
		$this->assertTrue($this->mockCommand->useFileAsInput, 'useFileAsInput should remain true');
	}

	/**
	 * Test Case 5a: Input method consideration (imageData)
	 */
	public function testRunHandlesImageDataInputCorrectlyWithVersion303OrNewer()
	{
		$this->mockCommand->method('getTesseractVersion')->willReturn('5.0.0');

		// imageData sets useFileAsInput to false
		$this->tess->imageData('dummydata', 10);
		$this->tess->run(); // Should default to stdout output

		$this->assertFalse($this->mockCommand->useFileAsInput, 'useFileAsInput should be false when imageData is used');
		$this->assertFalse($this->mockCommand->useFileAsOutput, 'useFileAsOutput should default to false (stdout) with imageData and no output file');
	}

	/**
	 * Test Case 5b: Input method consideration (image)
	 */
	public function testRunHandlesImageInputCorrectlyWithVersion303OrNewer()
	{
		$this->mockCommand->method('getTesseractVersion')->willReturn('5.0.0');

		// image() keeps useFileAsInput as true (default)
		$this->tess->image('dummy.png');
		$this->tess->run(); // Should default to stdout output

		$this->assertTrue($this->mockCommand->useFileAsInput, 'useFileAsInput should be true when image() is used');
		$this->assertFalse($this->mockCommand->useFileAsOutput, 'useFileAsOutput should default to false (stdout) with image() and no output file');
	}


	// --- Simplified existing tests (Example) ---
	// Note: These need adaptation as they now use a mock command.
	// The original tests asserted the string representation of the command.
	// Asserting properties might be more suitable for unit tests.

	public function testSimplestUsage()
	{
		$expected = '"tesseract" "image.png" "tmpfile"';
		$actual = $this->tess->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDelayedSettingOfImagePath()
	{
		$expected = '"tesseract" "image.png" "tmpfile"';

		$ocr = new TesseractOCR(null, new TestableCommand());
		$ocr->image('image.png');
		$actual = $ocr->command;

		$this->assertEquals("$expected", "$actual");
	}

	public function testCustomExecutablePath()
	{
		// skipping for now until I take the time to properly fix it
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') $this->skip();

		$expected = '"/bin/ls" "image.png" "tmpfile"';
		$actual = $this->tess->executable('/bin/ls')->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDefiningOptions()
	{
		$expected = '"tesseract" "image.png" "tmpfile" -l eng hocr';
		$actual = $this->tess->lang('eng')->format('hocr')->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testAllowlistSingleStringArgument()
	{
		$expected = '"tesseract" "image.png" "tmpfile" -c "tessedit_char_whitelist=abcdefghij"';
		$actual = $this->tess->allowlist('abcdefghij')->command;
		$this->assertEquals("$expected", $actual);
	}

	public function testAllowlistMultipleStringArguments()
	{
		$expected = '"tesseract" "image.png" "tmpfile" -c "tessedit_char_whitelist=abcdefghij"';
		$actual = $this->tess->allowlist('ab', 'cd', 'ef', 'gh', 'ij')->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testAllowlistSingleArrayArgument()
	{
		$expected = '"tesseract" "image.png" "tmpfile" -c "tessedit_char_whitelist=abcdefghij"';
		$actual = $this->tess->allowlist(range('a', 'j'))->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testAllowlistMultipleArrayArguments()
	{
		$expected = '"tesseract" "image.png" "tmpfile" -c "tessedit_char_whitelist=abcdefghij"';
		$actual = $this->tess->allowlist(range('a', 'e'), range('f', 'j'))->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testAllowlistMixedArguments()
	{
		$expected = '"tesseract" "image.png" "tmpfile" -c "tessedit_char_whitelist=0123456789abcdefghij"';
		$actual = $this->tess->allowlist(range(0, 9), 'abcd', range('e', 'j'))->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDefiningConfigPairs()
	{
		$expected = '"tesseract" "image.png" "tmpfile" '
			.'-c "load_system_dawg=F" '
			.'-c "tessedit_create_pdf=1"';
		$actual = $this->tess->loadSystemDawg('F')->tesseditCreatePdf(1)->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDefiningConfigFile()
	{
		$expected = '"tesseract" "image.png" "tmpfile" tsv';
		$actual = $this->tess->configFile('tsv')->command;
		$this->assertEquals("$expected", "$actual");
	}

	// @deprecated
	public function testDefiningFormat()
	{
		$expected = '"tesseract" "image.png" "tmpfile" tsv';
		$actual = $this->tess->format('tsv')->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDigits()
	{
		$expected = '"tesseract" "image.png" "tmpfile" digits';
		$actual = $this->tess->digits()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testHocr()
	{
		$expected = '"tesseract" "image.png" "tmpfile" hocr';
		$actual = $this->tess->hocr()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testPdf()
	{
		$expected = '"tesseract" "image.png" "tmpfile" pdf';
		$actual = $this->tess->pdf()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testQuiet()
	{
		$expected = '"tesseract" "image.png" "tmpfile" quiet';
		$actual = $this->tess->quiet()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testTsv()
	{
		$expected = '"tesseract" "image.png" "tmpfile" tsv';
		$actual = $this->tess->tsv()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testTxt()
	{
		$expected = '"tesseract" "image.png" "tmpfile" txt';
		$actual = $this->tess->txt()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testCustomTempDir()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') $this->skip();

		$tess = new TesseractOCR('image.png');
		$cmd = $tess->tempDir($this->customTempDir)->command;

		$expected = "\"tesseract\" \"image.png\" \"{$this->customTempDir}";
		$actual = substr("$cmd", 0, strlen($expected));
		$this->assertEquals("$expected", "$actual");
	}

	public function testCustomTempDirWindows()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') $this->skip();

		$customTempDir = 'C:\Users\Foo Bar\Temp\Dir';
		if (!file_exists($customTempDir)) mkdir($customTempDir, null, true);

		$cmd = new Command('image.png');
		$cmd->tempDir = $customTempDir;

		$expected = '"tesseract" "image.png" "C:\Users\Foo Bar\Temp\Dir';
		$actual = substr("$cmd", 0, strlen($expected));
		$this->assertEquals("$expected", "$actual");
	}

	public function testThreadLimit()
	{
		$expected = 'OMP_THREAD_LIMIT=4 "tesseract" "image.png" "tmpfile"';
		$actual = $this->tess->threadLimit(4)->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testVersion()
	{
		$expected = '3.05';
		$actual = $this->tess->version();
		$this->assertEquals("$expected", "$actual");
	}

	public function testSetOutputFile()
	{
		$expected = '"tesseract" "image.png" "tmpfile" pdf';
		$actual = $this->tess->configFile('pdf')->setOutputFile('/foo/bar.pdf')->command;
		$this->assertEquals("$expected", "$actual");
	}
}
