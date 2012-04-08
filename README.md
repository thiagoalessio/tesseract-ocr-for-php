# TesseractOCR for PHP

  A wrapper to work with TesseractOCR inside your PHP scripts.

## Instalation

  Just clone and put somewhere inside your project folder.

    $ cd myapp/vendor
    $ git clone git://github.com/thiagoalessio/tesseract-ocr-for-php.git

### Dependencies

  - [ImageMagick](http://www.imagemagick.org/)
  - [TesseractOCR](http://code.google.com/p/tesseract-ocr/)

  **IMPORTANT**: Make sure that `convert` and `tesseract` executables are 
  visible in your $PATH.
  If you're running PHP on a webserver, the user may be not you, but \_www or 
  similar.
  So a good tip is to add the following line in your code:

    putenv("PATH={getenv('PATH')}:/usr/local/bin");

## Usage

    <?php
    require_once '/path/to/tesseract-ocr-for-php/tesseract-ocr.php';
    
    $text = TesseractOCR::recognize('images/some-words.jpg');
    ?>

