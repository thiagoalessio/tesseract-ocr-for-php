# TesseractOCR for PHP

  A wrapper to work with TesseractOCR inside your PHP scripts.

## Instalation

  Just clone and put somewhere inside your project folder.

    $ cd myapp/vendor
    $ git clone git://github.com/thiagoalessio/tesseract-ocr-for-php.git

### Dependencies

-  [ImageMagick](http://www.imagemagick.org/)
-  [TesseractOCR](http://code.google.com/p/tesseract-ocr/)

**IMPORTANT**: Make sure that `convert` and `tesseract` executables are 
  visible in your $PATH.
  If you're running PHP on a webserver, the user may be not you, but \_www or 
  similar.
  So a good tip is to add the following line in your code:

    $path = getenv('PATH');
    putenv("PATH=$path:/usr/local/bin");

## Usage

    <?php
    require_once '/path/to/tesseract_ocr/tesseract_ocr.php';
    
    $text = TesseractOCR::recognize('images/some-words.jpg');
    ?>

### Inducing recognition

  Sometimes tesseract misunderstand some chars, such as:

    0 - O
    1 - l
    j - ,
    etc ...

  But you can improve recognition accuracy by specifing what kind of chars
  you're sending, for example:

    <?php
    require_once '/path/to/tesseract_ocr/tesseract_ocr.php';
    
    // tesseract will threat everything as downcase letters
    TesseractOCR::recognize('my-image.jpg', range('a','z'));
    
    // you can pass as many ranges as you need
    TesseractOCR::recognize('my-image.jpg', range(0,9), range('A','Z'));
    ?>

  You can even do *cool* stuff like this one:

    <?php
    require_once '/path/to/tesseract_ocr/tesseract_ocr.php';
    
    TesseractOCR::recognize('617.jpg', range('A','Z')); // will return "GIT"
    ?>

