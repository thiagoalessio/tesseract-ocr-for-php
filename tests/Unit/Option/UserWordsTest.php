<?php namespace thiagoalessio\TesseractOCR\Tests\Unit\Option;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Option\UserWords;

class UserWordsTest extends TestCase
{
	public function testSimplePath()
	{
		$expected = ' --user-words "/path/to/userwords"';
		$actual = new UserWords('/path/to/userwords');
		$this->assertEquals("$expected", "$actual");
	}

	public function testPathWithBackslashes()
	{
		$expected = ' --user-words "c:\\\\path\\\\to\\\\userwords"';
		$actual = new UserWords('c:\path\to\userwords');
		$this->assertEquals("$expected", "$actual");
	}
}
