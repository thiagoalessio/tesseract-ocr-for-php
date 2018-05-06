<?php namespace thiagoalessio\TesseractOCR\Tests\Unit;

use thiagoalessio\TesseractOCR\Command;

class TestableCommand extends Command
{
	protected function isVersion303()
	{
		return false;
	}
}
