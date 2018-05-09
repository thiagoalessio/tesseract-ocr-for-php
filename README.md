<img src="https://thiagoalessio.ams3.digitaloceanspaces.com/tesseract-ocr-for-php-logo.png" alt="Tesseract OCR for PHP" align="right" width="240px"/>

# Tesseract OCR for PHP

A wrapper to work with Tesseract OCR inside PHP.

[![Circle CI][circleci_badge]][circleci]
[![AppVeyor][appveyor_badge]][appveyor]
[![Codacy][codacy_badge]][codacy]
[![Test Coverage][test_coverage_badge]][test_coverage]
<br/>
[![Latest Stable Version][stable_version_badge]][packagist]
[![Total Downloads][total_downloads_badge]][packagist]
[![Monthly Downloads][monthly_downloads_badge]][packagist]
<br/>
[![Join the chat][gitter_badge]][gitter]
[![Tweet][twitter_badge]][tweet_intent]

## Installation

Via [Composer][]:

    $ composer require thiagoalessio/tesseract_ocr

:bangbang: **This library depends on [Tesseract OCR][], version _3.03_ or later.**

<br/>

### ![][windows_icon] Note for Windows users

There are [many ways][tesseract_installation_on_windows] to install
[Tesseract OCR][] on your system, but if you just want something quick to
get up and running, I recommend installing the [Capture2Text][] package with
[Chocolatey][].

    choco install capture2text --version 3.9

:warning: Recent versions of [Capture2Text][] stopped shipping the `tesseract` binary.

<br/>

### ![][macos_icon] Note for macOS users

With [MacPorts][] you can install support for individual languages, like so:

    $ sudo port install tesseract-<langcode>

But that is not possible with [Homebrew][]. It comes only with **English** support
by default, so if you intend to use it for other language, the quickest solution
is to install them all:

    $ brew install tesseract --with-all-languages

<br/>

## Usage

### Basic usage

<img align="right" width="50%" title="The quick brown fox jumps over the lazy dog." src="./tests/EndToEnd/images/text.png"/>

```php
use thiagoalessio\TesseractOCR\TesseractOCR;
echo (new TesseractOCR('text.png'))
    ->run();
```

```
The quick brown fox
jumps over
the lazy dog.
```

<br/>

### Other languages

<img align="right" width="50%" title="Bülowstraße" src="./tests/EndToEnd/images/german.png"/>

```php
use thiagoalessio\TesseractOCR\TesseractOCR;
echo (new TesseractOCR('german.png'))
    ->lang('deu')
    ->run();
```

```
Bülowstraße
```

<br/>

### Multiple languages

<img align="right" width="50%" title="I eat すし y Pollo" src="./tests/EndToEnd/images/mixed-languages.png"/>

```php
use thiagoalessio\TesseractOCR\TesseractOCR;
echo (new TesseractOCR('mixed-languages.png'))
    ->lang('eng', 'jpn', 'spa')
    ->run();
```

```
I eat すし y Pollo
```

<br/>

### Inducing recognition

<img align="right" width="50%" title="8055" src="./tests/EndToEnd/images/8055.png"/>

```php
use thiagoalessio\TesseractOCR\TesseractOCR;
echo (new TesseractOCR('8055.png'))
    ->whitelist(range('A', 'Z'))
    ->run();
```

```
BOSS
```

<br/>

### Breaking CAPTCHAs

Yes, I know some of you might want to use this library for the *noble* purpose
of breaking CAPTCHAs, so please take a look at this comment:

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

### tessdataDir

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
A complete list of available languages can be found at:
<https://github.com/tesseract-ocr/tesseract/blob/master/doc/tesseract.1.asc#languages>

__Tip from [@daijiale][]:__ Use the combination `->lang('chi_sim', 'chi_tra')`
for proper recognition of Chinese.

```php
 echo (new TesseractOCR('img.png'))
     ->lang('lang1', 'lang2', 'lang3')
     ->run();
```

### psm

Specify the Page Segmentation Method, which instructs `tesseract` how to
interpret the given image.

__More info:__ <https://github.com/tesseract-ocr/tesseract/wiki/ImproveQuality#page-segmentation-method>

```php
echo (new TesseractOCR('img.png'))
    ->psm(6)
    ->run();
```

### oem

Specify the OCR Engine Mode. (see `tesseract --help-oem`)

```php
echo (new TesseractOCR('img.png'))
    ->oem(2)
    ->run();
```

### whitelist

This is a shortcut for `->config('tessedit_char_whitelist', 'abcdef....')`.

```php
echo (new TesseractOCR('img.png'))
    ->whitelist(range('a', 'z'), range(0, 9), '-_@')
    ->run();
```

### configFile

Specify a config file to be used. It can either be the path to your own
config file or the name of one of the predefined config files:
<https://github.com/tesseract-ocr/tesseract/tree/master/tessdata/configs>

```php
echo (new TesseractOCR('img.png'))
    ->configFile('hocr')
    ->run();
```

### format (deprecated)

Specify an output format other than text.
Available options are [HOCR][] and [TSV][] (TSV is only available on Tesseract 3.05+)

```php
echo (new TesseractOCR('img.png'))
    ->format('hocr')
    ->run();
```

### digits

Shortcut for `->configFile('digits')`.

```php
echo (new TesseractOCR('img.png'))
    ->digits()
    ->run();
```

### hocr

Shortcut for `->configFile('hocr')`.

```php
echo (new TesseractOCR('img.png'))
    ->hocr()
    ->run();
```

### pdf

Shortcut for `->configFile('pdf')`.

```php
echo (new TesseractOCR('img.png'))
    ->pdf()
    ->run();
```

### quiet

Shortcut for `->configFile('quiet')`.

```php
echo (new TesseractOCR('img.png'))
    ->quiet()
    ->run();
```

### tsv

Shortcut for `->configFile('tsv')`.

```php
echo (new TesseractOCR('img.png'))
    ->tsv()
    ->run();
```

### txt

Shortcut for `->configFile('txt')`.

```php
echo (new TesseractOCR('img.png'))
    ->txt()
    ->run();
```

### Other options

Any configuration option offered by Tesseract can be used like that:

```php
echo (new TesseractOCR('img.png'))
    ->config('config_var', 'value')
    ->config('other_config_var', 'other value')
    ->run();
```

Or like that:

```php
echo (new TesseractOCR('img.png'))
    ->configVar('value')
    ->otherConfigVar('other value')
    ->run();
```

__More info:__ <https://github.com/tesseract-ocr/tesseract/wiki/ControlParams>

### Thread-limit

Sometimes, it may be useful to limit the number of threads that tesseract is
allowed to use (e.g. in [this case](https://github.com/tesseract-ocr/tesseract/issues/898)).
Set the maxmium number of threads as param for the run()-function:

```php
echo (new TesseractOCR('img.png'))
    ->run(1);
```

## Where to get help

Join the chat at <https://gitter.im/thiagoalessio/tesseract-ocr-for-php>

## How to contribute

See [CONTRIBUTING.md][].

## License

tesseract-ocr-for-php is released under the [MIT License][].


<h2></h2><p align="center"><sub>Made with <sub><a href="#"><img src="https://thiagoalessio.ams3.digitaloceanspaces.com/heart.svg" alt="love" width="14px"/></a></sub> in Berlin</sub></p>

[circleci_badge]: https://circleci.com/gh/thiagoalessio/tesseract-ocr-for-php/tree/master.svg?style=shield
[circleci]: https://circleci.com/gh/thiagoalessio/workflows/tesseract-ocr-for-php/tree/master
[appveyor_badge]: https://ci.appveyor.com/api/projects/status/xwy5ls0798iwcim3/branch/master?svg=true
[appveyor]: https://ci.appveyor.com/project/thiagoalessio/tesseract-ocr-for-php/branch/master
[codacy_badge]: https://api.codacy.com/project/badge/Grade/024c8814aecf40329500df267134c623
[codacy]: https://www.codacy.com/app/thiagoalessio/tesseract-ocr-for-php?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=thiagoalessio/tesseract-ocr-for-php&amp;utm_campaign=Badge_Grade
[test_coverage_badge]: https://api.codacy.com/project/badge/Coverage/024c8814aecf40329500df267134c623
[test_coverage]: https://www.codacy.com/app/thiagoalessio/tesseract-ocr-for-php?utm_source=github.com&utm_medium=referral&utm_content=thiagoalessio/tesseract-ocr-for-php&utm_campaign=Badge_Coverage
[stable_version_badge]: https://img.shields.io/packagist/v/thiagoalessio/tesseract_ocr.svg
[packagist]: https://packagist.org/packages/thiagoalessio/tesseract_ocr
[total_downloads_badge]: https://img.shields.io/packagist/dt/thiagoalessio/tesseract_ocr.svg
[monthly_downloads_badge]: https://img.shields.io/packagist/dm/thiagoalessio/tesseract_ocr.svg
[gitter_badge]: https://img.shields.io/gitter/room/thiagoalessio/tesseract-ocr-for-php.svg?logo=gitter-white&colorB=33cc99
[gitter]: https://gitter.im/thiagoalessio/tesseract-ocr-for-php?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge
[twitter_badge]: https://img.shields.io/twitter/url/https/github.com/thiagoalessio/tesseract-ocr-for-php.svg?style=social&logo=twitter
[tweet_intent]: https://twitter.com/intent/tweet?text=tesseract-ocr-for-php%3A%20A%20wrapper%20to%20work%20with%20Tesseract%20OCR%20inside%20PHP.&url=https://github.com/thiagoalessio/tesseract-ocr-for-php&hashtags=php,tesseract,ocr
[Tesseract OCR]: https://github.com/tesseract-ocr/tesseract
[Composer]: http://getcomposer.org/
[windows_icon]: https://thiagoalessio.ams3.digitaloceanspaces.com/windows-18.svg
[macos_icon]: https://thiagoalessio.ams3.digitaloceanspaces.com/apple-18.svg
[tesseract_installation_on_windows]: https://github.com/tesseract-ocr/tesseract/wiki#windows
[Capture2Text]: https://chocolatey.org/packages/capture2text
[Chocolatey]: https://chocolatey.org
[MacPorts]: https://www.macports.org
[Homebrew]: https://brew.sh
[@daijiale]: https://github.com/daijiale
[HOCR]: https://github.com/tesseract-ocr/tesseract/wiki/Command-Line-Usage#hocr-output
[TSV]: https://github.com/tesseract-ocr/tesseract/wiki/Command-Line-Usage#tsv-output-currently-available-in-305-dev-in-master-branch-on-github
[CONTRIBUTING.md]: https://github.com/thiagoalessio/tesseract-ocr-for-php/blob/master/CONTRIBUTING.md
[MIT License]: https://github.com/thiagoalessio/tesseract-ocr-for-php/blob/master/MIT-LICENSE
