<?php
/**
 * End-to-end tests, just to get a feeling of how a real user would interact
 * with this library.
 */
use PHPUnit\Framework\TestCase;

class FunctionalTests extends TestCase
{
    /**
     * Recognizing text from an image.
     */
    public function testBasicUsage()
    {
        $expected = "The quick brown fox\njumps over\nthe lazy dog.";

        $actual = (new TesseractOCR(__DIR__.'/../images/text.png'))
            ->run();

        $this->assertEquals($expected, $actual);
    }

    /**
     * Should work fine even if image name contains special characters.
     */
    public function testImageNameWithSpecialCharacters()
    {
        $expected = "Issue found by\n@crimsonvspurple";

        $actual = (new TesseractOCR(__DIR__.'/img name$with@special#chars.png'))
            ->run();

        $this->assertEquals($expected, $actual);
    }

    public function testOtherLanguages()
    {
        $expected = 'Bülowstraße';

        $actual = (new TesseractOCR(__DIR__.'/../images/german.png'))
            ->lang('deu')
            ->run();

        $this->assertEquals($expected, $actual);
    }

    public function testMultipleLanguages()
    {
        $expected = 'I eat すし y Pollo';

        $actual = (new TesseractOCR(__DIR__.'/../images/mixed-languages.png'))
            ->lang('eng', 'jpn', 'spa')
            ->run();

        $this->assertEquals($expected, $actual);
    }

    public function testInducingRecognition()
    {
        $expected = 'BOSS';

        $actual = (new TesseractOCR(__DIR__.'/../images/8055.png'))
            ->whitelist(range('A', 'Z'))
            ->run();

        $this->assertEquals($expected, $actual);
    }
}
