![Tesseract OCR for PHP logo: A baby elephant sucking letters from a book][0]
# Tesseract OCR for PHP

A wrapper to work with Tesseract OCR inside PHP.

## Installation

First of all, make sure you have [Tesseract OCR][1] installed.

### As a [composer][2] dependency

    {
        "require": {
            "thiagoalessio/tesseract_ocr": "1.*"
        }
    }

## Usage

### Basic usage

Given the following image ([text.png][3]):

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

Given the following image ([german.png][4]):

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

Given the following image ([multi-languages.png][5]):

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

They need to be specified as 3-character [ISO 639-2][7] language codes.

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

### `->whitelist(range('a', 'z'), range(0, 9), '-_@')`

This is a shortcut for `->config('tessedit_char_whitelist', 'abcdef....')`.

## Where to get help

* [#tesseract-ocr-for-php on freenode IRC][9]

## License

[Apache License 2.0][8].

[0]: http://thiagoalessio.me/content/images/logo.png
[1]: https://github.com/tesseract-ocr/tesseract/wiki
[2]: http://getcomposer.org/
[3]: http://thiagoalessio.me/content/images/text.png
[4]: http://thiagoalessio.me/content/images/german.png
[5]: http://thiagoalessio.me/content/images/multi-languages.png
[6]: http://thiagoalessio.me/content/images/8055.png
[7]: https://www.loc.gov/standards/iso639-2/php/code_list.php
[8]: https://github.com/thiagoalessio/tesseract-ocr-for-php/blob/master/LICENSE
[9]: irc://irc.freenode.net/tesseract-ocr-for-php
