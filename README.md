# TesseractOCR for PHP

  A wrapper to work with TesseractOCR inside your PHP scripts.

## Instalation

  Via [composer](http://getcomposer.org/)
  (https://packagist.org/packages/thiagoalessio/tesseract_ocr)

    {
        "require": {
            "thiagoalessio/tesseract_ocr": ">= 0.2.0"
        }
    }

  Or just clone and put somewhere inside your project folder.

    $ cd myapp/vendor
    $ git clone git://github.com/thiagoalessio/tesseract-ocr-for-php.git

### Dependencies

-  [TesseractOCR](http://code.google.com/p/tesseract-ocr/)

**IMPORTANT**: Make sure that the `tesseract` binary is on your $PATH.
  If you're running PHP on a webserver, the user may be not you, but \_www or 
  similar.
  If you need, there is always the possibility of modify your $PATH:

    $path = getenv('PATH');
    putenv("PATH=$path:/usr/local/bin");

### Windows users

I received several messages from people trying to get this library running
under Windows, so I decided to write a short tutorial that can be found 
[here](http://thiagoalessio.me/tesseractocr-for-php-on-windows/).

## Usage

### Basic usage

    <?php
    require_once '/path/to/TesseractOCR/TesseractOCR.php';
    //or require_once 'vendor/autoload.php' if you are using composer
    
    $tesseract = new TesseractOCR('images/some-words.jpg');
    echo $tesseract->recognize();

### Defining language

Tesseract has training data for several languages, which certainly improve
the accuracy of the recognition.

    <?php
    require_once '/path/to/TesseractOCR/TesseractOCR.php';
    //or require_once 'vendor/autoload.php' if you are using composer
    
    $tesseract = new TesseractOCR('images/sind-sie-deutsch.jpg');
    $tesseract->setLanguage('deu'); //same 3-letters code as tesseract training data packages
    echo $tesseract->recognize();

### Inducing recognition

  Sometimes tesseract misunderstand some chars, such as:

    0 - O
    1 - l
    j - ,
    etc ...

  But you can improve recognition accuracy by specifing what kind of chars
  you're sending, for example:

    <?php
    $tesseract = new TesseractOCR('my-image.jpg');
    $tesseract->setWhitelist(range('a','z')); //tesseract will threat everything as downcase letters
    echo $tesseract->recognize();
    
    $tesseract = new TesseractOCR('my-image.jpg');
    $tesseract->setWhitelist(range('A','Z'), range(0,9), '_-@.'); //you can pass as many ranges as you need

  You can even do *cool* stuff like this one:

    <?php
    $tesseract = new TesseractOCR('617.jpg');
    $tesseract->setWhitelist(range('A','Z'));
    echo $tesseract->recognize(); //will return "GIT"

## Troubleshooting

#### Warnings like `Permission denied` or `No such file or directory`

  To solve this issue you can specify a custom directory for temp files:

    <?php
    $tesseract = new TesseractOCR('my-image.jpg');
    $tesseract->setTempDir('./my-temp-dir');
