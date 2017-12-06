![Tesseract OCR for PHP logo: A baby elephant reading a book][project_logo]

# Tesseract OCR for PHP

[![Total Downloads][total_downloads_badge]][packagist]
[![Build Status][build_status_badge]][travisci]
[![Code Climate][codeclimate_badge]][codeclimate]
[![Test Coverage][test_coverage_badge]][test_coverage]

A wrapper to work with Tesseract OCR inside PHP.

## Installation

First of all, make sure you have [Tesseract OCR][] installed. (**v3.03** or greater)
Please also check the [FAQ for Windows Users][] Wiki Page, if needed.

### As a [Composer][] dependency

```json
{
    "require": {
        "thiagoalessio/tesseract_ocr": "1.2.3"
    }
}
```

## Usage

### Basic usage

Given the following image:

![The quick brown fox jumps over the lazy dog][quick_brown_fox]

And the following code:

```php
echo (new TesseractOCR('text.png'))
    ->run();
```

Produces:

```
The quick brown fox
jumps over
the lazy dog.
```

### Other languages

Given the following image:

![grüßen - Google Translate said it means "to greet" in German][german]

And the following code:

```php
echo (new TesseractOCR('german.png'))
    ->run();
```

Produces `BiilowstraBe`.

Which is not good, but defining a language:

```php
echo (new TesseractOCR('german.png'))
    ->lang('deu')
    ->run();
```

Produces `Bülowstraße`.

### Multiple languages

Given the following image:

![The phrase "I each apple sushi", with mixed English, Japanese and Portuguese][multilanguage]

And the following code:

```php
echo (new TesseractOCR('multi-language.png'))
    ->lang('eng', 'jpn', 'por')
    ->run();
```

Produces `I eat すし de maçã`.

### Inducing recognition

Given the following image:

![Number 8055][8055]

And the following code:

```php
echo (new TesseractOCR('8055.png'))
    ->whitelist(range('A', 'Z'))
    ->run();
```

Produces `BOSS`.

### Breaking CAPTCHAs

Yes, I know some of you might want to use this library for the *noble* purpose
of breaking CAPTCHAs, so please take a look on this comment:

<https://github.com/thiagoalessio/tesseract-ocr-for-php/issues/91#issuecomment-342290510>

## API

### executable

Define a custom location of the `tesseract` executable,
if by any reason it is not present in the `$PATH`.

```php
echo (new TesseractOCR('img.png'))
    ->executable('/path/to/tesseract')
    ->run();
```

### path

Specify a custom location for the tessdata directory.

```php
echo (new TesseractOCR('img.png'))
    ->tessdataDir('/path')
    ->run();
```

### userWords

Specify the location of user words file.

This is a plain text file containing a list of words that you want to be
considered as a normal dictionary words by `tesseract`.

Useful when dealing with contents that contain technical terminology, jargon,
etc.

```
$ cat /path/to/user-words.txt
foo
bar
```

```php
echo (new TesseractOCR('img.png'))
    ->userWords('/path/to/user-words.txt')
    ->run();
```

### userPatterns

Specify the location of user patterns file.

If the contents you are dealing with have known patterns, this option can help
a lot tesseract's recognition accuracy.

```
$ cat /path/to/user-patterns.txt'
1-\d\d\d-GOOG-441
www.\n\\\*.com
```

```php
echo (new TesseractOCR('img.png'))
    ->userPatterns('/path/to/user-patterns.txt')
    ->run();
```

### lang

Define one or more languages to be used during the recognition.
A complete list of available languages can be found [here][tesseract_langs].

__Tip from [@daijiale][]:__ Use the combination `->lang('chi_sim', 'chi_tra')`
for proper recognition of Chinese.

```php
 echo (new TesseractOCR('img.png'))
     ->lang('lang1', 'lang2', 'lang3')
     ->run();
```

### psm

Specify the Page Segmentation Mode, which instructs `tesseract` how to
interpret the given image.

Possible `psm` values are:

| Value | Description                                               |
| -----:| --------------------------------------------------------- |
| 0     | Orientation and script detection (OSD) only.              |
| 1     | Automatic page segmentation with OSD.                     |
| 2     | Automatic page segmentation, but no OSD, or OCR.          |
| 3     | Fully automatic page segmentation, but no OSD. (Default)  |
| 4     | Assume a single column of text of variable sizes.         |
| 5     | Assume a single uniform block of vertically aligned text. |
| 6     | Assume a single uniform block of text.                    |
| 7     | Treat the image as a single text line.                    |
| 8     | Treat the image as a single word.                         |
| 9     | Treat the image as a single word in a circle.             |
| 10    | Treat the image as a single character.                    |

```php
echo (new TesseractOCR('img.png'))
    ->psm(6)
    ->run();
```

### whitelist

This is a shortcut for `->config('tessedit_char_whitelist', 'abcdef....')`.

```php
echo (new TesseractOCR('img.png'))
    ->whitelist(range('a', 'z'), range(0, 9), '-_@')
    ->run();
```

### Other options

Tesseract offers incredible control to the user through its 600+ configuration options.
You can see the complete list by running the following command:

```
$ tesseract --print-parameters
Tesseract parameters:
... long list with all parameters ...
```

```php
echo (new TesseractOCR('img.png'))
    ->config('config_var', 'value')
    ->config('other_config_var', 'other value')
    ->run();

// or better yet, just cammel case any of the options:

echo (new TesseractOCR('img.png'))
    ->configVar('value')
    ->otherConfigVar('other value')
    ->run();
```

## Where to get help

`#tesseract-ocr-for-php` on freenode IRC.

## License

[Apache License 2.0][].

[project_logo]: https://raw.githubusercontent.com/thiagoalessio/tesseract-ocr-for-php/master/images/logo.png
[total_downloads_badge]: https://poser.pugx.org/thiagoalessio/tesseract_ocr/downloads
[packagist]: https://packagist.org/packages/thiagoalessio/tesseract_ocr
[build_status_badge]: https://travis-ci.org/thiagoalessio/tesseract-ocr-for-php.svg?branch=master
[travisci]: https://travis-ci.org/thiagoalessio/tesseract-ocr-for-php
[codeclimate_badge]: https://api.codeclimate.com/v1/badges/3eff8345313848d863c0/maintainability
[codeclimate]: https://codeclimate.com/github/thiagoalessio/tesseract-ocr-for-php/maintainability
[test_coverage_badge]: https://api.codeclimate.com/v1/badges/3eff8345313848d863c0/test_coverage
[test_coverage]: https://codeclimate.com/github/thiagoalessio/tesseract-ocr-for-php/test_coverage
[Tesseract OCR]: https://github.com/tesseract-ocr/tesseract/wiki
[FAQ for Windows Users]:https://github.com/thiagoalessio/tesseract-ocr-for-php/wiki/FAQ-for-Windows-Users
[Composer]: http://getcomposer.org/
[quick_brown_fox]: https://raw.githubusercontent.com/thiagoalessio/tesseract-ocr-for-php/master/images/text.png
[german]: https://raw.githubusercontent.com/thiagoalessio/tesseract-ocr-for-php/master/images/german.png
[multilanguage]: https://raw.githubusercontent.com/thiagoalessio/tesseract-ocr-for-php/master/images/multi-language.png
[8055]: https://raw.githubusercontent.com/thiagoalessio/tesseract-ocr-for-php/master/images/8055.png
[tesseract_langs]: https://github.com/tesseract-ocr/tesseract/blob/master/doc/tesseract.1.asc#languages
[@daijiale]: https://github.com/daijiale
[Apache License 2.0]: https://github.com/thiagoalessio/tesseract-ocr-for-php/blob/master/LICENSE
