<?php
require_once dirname(__FILE__).'/../tesseract_ocr/tesseract_ocr.php';

class TesseractOCRTest extends PHPUnit_Framework_TestCase {

  var $imagesPath;

  function setUp() {
    $path = getenv('PATH');
    putenv("PATH=$path:/usr/local/bin");
    $this->imagesPath = dirname(__FILE__).'/images';
  }

  function testTextRecognition() {
    $images = array(
      'image1.jpg' => 'Hello, Tesseract!',
      'image2.gif' => 'Works with a GIF image',
      'image3.png' => 'A PNG? Recognizes too!'
    );
    foreach($images as $path => $text){
      $this->assertEquals(
        TesseractOCR::recognize("\"{$this->imagesPath}/$path\""),
        $text
      );
    }
  }

  function testInducingRecognition() {
    $this->assertEquals(
      TesseractOCR::recognize("\"{$this->imagesPath}/617.jpg\"", range('A','Z')),
      'GIT'
    );
    $this->assertEquals(
      TesseractOCR::recognize("\"{$this->imagesPath}/gotz.jpg\"", range(0,9)),
      '6072'
    );
  }
}
