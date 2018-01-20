<?php namespace thiagoalessio\TesseractOCR\Tests\Unit\Option;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Option\UserPatterns;

class UserPatternsTest extends TestCase
{
	public function testSimplePath()
	{
		$expected = ' --user-patterns "/path/to/userpatterns"';
		$actual = new UserPatterns('/path/to/userpatterns');
		$this->assertEquals("$expected", "$actual");
	}

	public function testPathWithBackslashes()
	{
		$expected = ' --user-patterns "c:\\\\path\\\\to\\\\userpatterns"';
		$actual = new UserPatterns('c:\path\to\userpatterns');
		$this->assertEquals("$expected", "$actual");
	}
}
