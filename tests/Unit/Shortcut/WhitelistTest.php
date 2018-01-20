<?php namespace thiagoalessio\TesseractOCR\Tests\Unit\Shortcut;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Shortcut\Whitelist;

class WhitelistTest extends TestCase
{
	public function testSingleStringArgument()
	{
		$expected = ' -c "tessedit_char_whitelist=abcdefghij"';
		$actual = Whitelist::buildOption('abcdefghij');
		$this->assertEquals("$expected", "$actual");
	}

	public function testMultipleStringArguments()
	{
		$expected = ' -c "tessedit_char_whitelist=abcdefghij"';
		$actual = Whitelist::buildOption('ab', 'cd', 'ef', 'gh', 'ij');
		$this->assertEquals("$expected", "$actual");
	}

	public function testSingleArrayArgument()
	{
		$expected = ' -c "tessedit_char_whitelist=abcdefghij"';
		$actual = Whitelist::buildOption(range('a', 'j'));
		$this->assertEquals("$expected", "$actual");
	}

	public function testMultipleArrayArguments()
	{
		$expected = ' -c "tessedit_char_whitelist=abcdefghij"';
		$actual = Whitelist::buildOption(range('a', 'e'), range('f', 'j'));
		$this->assertEquals("$expected", "$actual");
	}

	public function testMixedArguments()
	{
		$expected = ' -c "tessedit_char_whitelist=0123456789abcdefghij"';
		$actual = Whitelist::buildOption(range(0, 9), 'abcd', range('e', 'j'));
		$this->assertEquals("$expected", "$actual");
	}
}
