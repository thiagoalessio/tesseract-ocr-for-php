<?php
require_once dirname(__FILE__).'/../TesseractOCR/TesseractOCR.php';

class TesseractOCRTest extends PHPUnit_Framework_TestCase
{
    protected $imagesDir;

    public function setUp()
    {
        $this->imagesDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
        $this->imagesDir.= 'images'.DIRECTORY_SEPARATOR;
    }

    public function testBasicRecognition()
    {
        $tesseract = new TesseractOCR("{$this->imagesDir}hello.png");
        $expectedText = 'Hello Tesseract OCR, welcome to PHP';
        $this->assertEquals($expectedText, $tesseract->recognize());
    }

    public function testInducedRecognition()
    {
        $tesseract = new TesseractOCR("{$this->imagesDir}gotz.png");
        $tesseract->setWhitelist(range(0,9));
        $this->assertEquals(6012, $tesseract->recognize());
    }

    public function testSpecificLanguageRecognition()
    {
        $tesseract = new TesseractOCR("{$this->imagesDir}german.png");
        $tesseract->setLanguage('deu');
        $this->assertEquals('grüßen in Deutsch', $tesseract->recognize());
    }
}
