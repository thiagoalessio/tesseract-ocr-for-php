<?php
/**
 * End-to-end tests, just to get a feeling of how a real user would interact
 * with this library.
 */
class FunctionalTests extends PHPUnit_Framework_TestCase
{
    /**
     * Recognizing text from an image.
     */
    public function testRecognizingTextFromImage()
    {
        $expected = "The quick brown fox\njumps over the lazy\ndog.";

        $actual = (new TesseractOCR(__DIR__.'/text.png'))
            ->suppressErrors()
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
            ->suppressErrors()
            ->run();

        $this->assertEquals($expected, $actual);
    }
}
