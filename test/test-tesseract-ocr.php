<?php
require_once dirname(__FILE__).'/../vendor/poor-test/poor-test.php';
require_once dirname(__FILE__).'/../tesseract-ocr.php';

class TestTesseractOCR extends PoorTest {

  var $imagesPath;

  function beforeAll() {
    @putenv("PATH={getenv('PATH')}:/usr/local/bin");
    $this->imagesPath = dirname(__FILE__).'/images';
  }

  function testTextRecognition() {
    $images = array(
      'image1.jpg' => 'Hello, Tesseract!',
      'image2.gif' => 'Works with a GIF image',
      'image3.png' => 'A PNG? Recognizes too!'
    );
    foreach($images as $path => $text){
      if(TesseractOCR::recognize("{$this->imagesPath}/$path") != $text)
        return false;
    }
    return true;
  }
}
new TestTesseractOCR();
?>
