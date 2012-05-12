<?php
require_once dirname(__FILE__).'/../vendor/poor-test/poor-test.php';
require_once dirname(__FILE__).'/../tesseract_ocr/tesseract_ocr.php';

class TestTesseractOCR extends PoorTest {

  var $imagesPath;

  function beforeAll() {
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
      if(TesseractOCR::recognize("{$this->imagesPath}/$path") != $text)
        return false;
    }
    return true;
  }

  function testInducingRecognition() {
    $a = TesseractOCR::recognize($this->imagesPath.'/617.jpg', range('A','Z'));
    $b = TesseractOCR::recognize($this->imagesPath.'/gotz.jpg', range(0,9));
    return ($a == 'GIT') && ($b == '6072');
  }
}
new TestTesseractOCR();
?>
