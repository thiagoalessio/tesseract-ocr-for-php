<?php namespace thiagoalessio\TesseractOCR\Tests\Unit\Option;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Option\Config;

class ConfigTest extends TestCase
{
	public function testSimpleConfigPair()
	{
		$expected = ' -c "var=value"';
		$actual = new Config('var', 'value');
		$this->assertEquals("$expected", "$actual");
	}

	public function testValueWithDoubleQuotes()
	{
		$expected = ' -c "chars=\'\\"!$@%&?`"';
		$actual = new Config('chars', '\'"!$@%&?`');
		$this->assertEquals("$expected", "$actual");
	}

	public function testConvertCamelCaseVarToSnakeCase()
	{
		$expected = ' -c "foo_bar_baz_chunky_bacon=value"';
		$actual = new Config('fooBarBazChunkyBacon', 'value');
		$this->assertEquals("$expected", "$actual");
	}
}
