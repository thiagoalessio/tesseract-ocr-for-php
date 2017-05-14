![Tesseract OCR for PHP logo: A baby elephant sucking letters from a book][0]

# Tesseract OCR for PHP

A wrapper to work with Tesseract OCR inside PHP.

[![Total Downloads](https://poser.pugx.org/thiagoalessio/tesseract_ocr/downloads)](https://packagist.org/packages/thiagoalessio/tesseract_ocr)
[![Build Status](https://travis-ci.org/thiagoalessio/tesseract-ocr-for-php.svg?branch=master)](https://travis-ci.org/thiagoalessio/tesseract-ocr-for-php)
[![Code Climate](https://codeclimate.com/github/thiagoalessio/tesseract-ocr-for-php/badges/gpa.svg)](https://codeclimate.com/github/thiagoalessio/tesseract-ocr-for-php)
[![Test Coverage](https://codeclimate.com/github/thiagoalessio/tesseract-ocr-for-php/badges/coverage.svg)](https://codeclimate.com/github/thiagoalessio/tesseract-ocr-for-php/coverage)

## Installation

First of all, make sure you have [Tesseract OCR][1] installed. (**v3.03** or greater)

### As a [composer][2] dependency

    {
        "require": {
            "thiagoalessio/tesseract_ocr": "1.0.0"
        }
    }

## Usage

### Basic usage

Given the following image ([text.jpeg][3]):

![The quick brown fox jumps over the lazy dog][3]

And the following code:

    <?php
    echo (new TesseractOCR('text.png'))
        ->run();

The output would be:

    The quick brown fox
    jumps over the lazy
    dog.

### Other languages

Given the following image ([german.jpeg][4]):

![grüßen - Google Translate said it means "to greet" in German][4]

And the following code:

    <?php
    echo (new TesseractOCR('german.png'))
        ->run();

The output would be:

    griiﬁen

Which is not good, but defining a language:

    <?php
    echo (new TesseractOCR('german.png'))
        ->lang('deu')
        ->run();

Will produce:

    grüßen

### Multiple languages

Given the following image ([multi-languages.jpeg][5]):

![The phrase "I each apple sushi", with mixed English, Japanese and Portuguese][5]

And the following code ....

    <?php
    echo (new TesseractOCR('multi-languages.png'))
        ->lang('eng', 'jpn', 'por')
        ->run();

The output would be:

    I eat 寿司 de maçã

### Inducing recognition

Given the following image ([8055.png][6]):

![Number 8055][6]

And the following code ....

    <?php
    echo (new TesseractOCR('8055.png'))
        ->whitelist(range('A', 'Z'))
        ->run();

The output would be:

    BOSS

### Quiet Mode

To clean the bash log console you can use the Quiet Mode configuration.
The following code:

    <?php
    echo (new TesseractOCR('text.png'))
        ->quietMode(true)
        ->run();

This way you can get clean logs.

## API

### `->executable('/path/to/tesseract')`

Define a custom location of the `tesseract` executable, if by any reason it is
not present in the `$PATH`.

### `->tessdataDir('/path')`

Specify a custom location for the tessdata directory.

### `->userWords('/path/to/user-words.txt')`

Specify the location of user words file.

This is a plain text file containing a list of words that you want to be
considered as a normal dictionary words by `tesseract`.

Useful when dealing with contents that contain technical terminology, jargon,
etc.

Example of a user words file:

    $ cat /path/to/user-words.txt
    foo
    bar

### `->userPatterns('/path/to/user-patterns.txt')`

Specify the location of user patterns file.

If the contents you are dealing with have known patterns, this option can help
a lot tesseract's recognition accuracy.

Example of a user patterns file:

    $ cat /path/to/user-patterns.txt'
    1-\d\d\d-GOOG-441
    www.\n\\\*.com

### `->lang('lang1', 'lang2', 'lang3')`

Define one or more languages to be used during the recognition.
A complete list of available languages can be found at
https://github.com/tesseract-ocr/tesseract/blob/master/doc/tesseract.1.asc#languages

__Tip from [@daijiale][10]:__ Use the combination `->lang('chi_sim', 'chi_tra')`
for proper recognition of Chinese.

### `->psm(6)`

Specify the Page Segmentation Mode, which instructs `tesseract` how to
interpret the given image.

Possible `psm` values are:

     0 = Orientation and script detection (OSD) only.
     1 = Automatic page segmentation with OSD.
     2 = Automatic page segmentation, but no OSD, or OCR.
     3 = Fully automatic page segmentation, but no OSD. (Default)
     4 = Assume a single column of text of variable sizes.
     5 = Assume a single uniform block of vertically aligned text.
     6 = Assume a single uniform block of text.
     7 = Treat the image as a single text line.
     8 = Treat the image as a single word.
     9 = Treat the image as a single word in a circle.
    10 = Treat the image as a single character.

### `->config('configvar', 'value')`

Tesseract offers incredible control to the user through its 660 configuration
vars.

You can see the complete list by running the following command:

    $ tesseract --print-parameters
    Tesseract parameters:
    ... long list with all parameters ...

### `->whitelist(range('a', 'z'), range(0, 9), '-_@')`

This is a shortcut for `->config('tessedit_char_whitelist', 'abcdef....')`.

## Where to get help

* [#tesseract-ocr-for-php on freenode IRC][9]

## License

[Apache License 2.0][8].

[0]: https://raw.githubusercontent.com/thiagoalessio/tesseract-ocr-for-php/master/images/logo.jpeg
[1]: https://github.com/tesseract-ocr/tesseract/wiki
[2]: http://getcomposer.org/
[3]: https://raw.githubusercontent.com/thiagoalessio/tesseract-ocr-for-php/master/images/text.jpeg
[4]: https://raw.githubusercontent.com/thiagoalessio/tesseract-ocr-for-php/master/images/german.jpeg
[5]: https://raw.githubusercontent.com/thiagoalessio/tesseract-ocr-for-php/master/images/multi-languages.jpeg
[6]: https://raw.githubusercontent.com/thiagoalessio/tesseract-ocr-for-php/master/images/8055.png
[7]: https://www.loc.gov/standards/iso639-2/php/code_list.php
[8]: https://github.com/thiagoalessio/tesseract-ocr-for-php/blob/master/LICENSE
[9]: irc://irc.freenode.net/tesseract-ocr-for-php
[10]: https://github.com/daijiale
