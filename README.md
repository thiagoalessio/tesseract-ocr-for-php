<img src="https://thiagoalessio.github.io/tesseract-ocr-for-php/images/logo.png" alt="Tesseract OCR for PHP" align="right" width="320px"/>

# Tesseract OCR for PHP

A wrapper to work with Tesseract OCR inside PHP.

[![CI][ci_badge]][ci]
[![AppVeyor][appveyor_badge]][appveyor]
[![Codacy][codacy_badge]][codacy]
[![Test Coverage][test_coverage_badge]][test_coverage]
<br/>
[![Latest Stable Version][stable_version_badge]][packagist]
[![Total Downloads][total_downloads_badge]][packagist]
[![Monthly Downloads][monthly_downloads_badge]][packagist]

## Installation

Via [Composer][]:

    $ composer require thiagoalessio/tesseract_ocr

:bangbang: **This library depends on [Tesseract OCR][], version _3.02_ or later.**

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

    $ brew install tesseract tesseract-lang

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
    ->allowlist(range('A', 'Z'))
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

### run

Executes a `tesseract` command, optionally receiving an integer as `timeout`,
in case you experience stalled tesseract processes.

```php
$ocr = new TesseractOCR();
$ocr->run();
```
```php
$ocr = new TesseractOCR();
$timeout = 500;
$ocr->run($timeout);
```

### image

Define the path of an image to be recognized by `tesseract`.

```php
$ocr = new TesseractOCR();
$ocr->image('/path/to/image.png');
$ocr->run();
```

### imageData

Set the image to be recognized by `tesseract` from a string, with its size.
This can be useful when dealing with files that are already loaded in memory.
You can easily retrieve the image data and size of an image object :
```php
//Using Imagick
$data = $img->getImageBlob();
$size = $img->getImageLength();
//Using GD
ob_start();
// Note that you can use any format supported by tesseract
imagepng($img, null, 0);
$size = ob_get_length();
$data = ob_get_clean();

$ocr = new TesseractOCR();
$ocr->imageData($data, $size);
$ocr->run();
```

### executable

Define a custom location of the `tesseract` executable,
if by any reason it is not present in the `$PATH`.

```php
echo (new TesseractOCR('img.png'))
    ->executable('/path/to/tesseract')
    ->run();
```

### version

Returns the current version of `tesseract`.

```php
echo (new TesseractOCR())->version();
```

### availableLanguages

Returns a list of available languages/scripts.

```php
foreach((new TesseractOCR())->availableLanguages() as $lang) echo $lang;
```

__More info:__ <https://github.com/tesseract-ocr/tesseract/blob/master/doc/tesseract.1.asc#languages-and-scripts>

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

### dpi

Specify the image DPI. It is useful if your image does not contain this information in its metadata.

```php
echo (new TesseractOCR('img.png'))
    ->dpi(300)
    ->run();
```

### allowlist

This is a shortcut for `->config('tessedit_char_whitelist', 'abcdef....')`.

```php
echo (new TesseractOCR('img.png'))
    ->allowlist(range('a', 'z'), range(0, 9), '-_@')
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

### setOutputFile

Specify an Outputfile to be used. Be aware: If you set an outputfile then
the option `withoutTempFiles` is ignored.
Tempfiles are written (and deleted) even if `withoutTempFiles = true`.

In combination with `configFile` you are able to get the `hocr`, `tsv` or
`pdf` files.

```php
echo (new TesseractOCR('img.png'))
    ->configFile('pdf')
    ->setOutputFile('/PATH_TO_MY_OUTPUTFILE/searchable.pdf')
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

### tempDir

Define a custom directory to store temporary files generated by tesseract.
Make sure the directory actually exists and the user running `php` is allowed
to write in there.

```php
echo (new TesseractOCR('img.png'))
    ->tempDir('./my/custom/temp/dir')
    ->run();
```

### withoutTempFiles

Specify that `tesseract` should output the recognized text without writing to temporary files.
The data is gathered from the standard output of `tesseract` instead.

```php
echo (new TesseractOCR('img.png'))
    ->withoutTempFiles()
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
Set the maxmium number of threads as param for the `run` function:

```php
echo (new TesseractOCR('img.png'))
    ->threadLimit(1)
    ->run();
```

## How to contribute

You can contribute to this project by:

* Opening an [Issue][] if you found a bug or wish to propose a new feature;
* Placing a [Pull Request][] with code that fix a bug, missing/wrong documentation
  or implement a new feature;

Just make sure you take a look at our [Code of Conduct][] and [Contributing][]
instructions.

## License

tesseract-ocr-for-php is released under the [MIT License][].


<h2></h2><p align="center"><sub>Made with <sub><a href="#"><img src="https://thiagoalessio.github.io/tesseract-ocr-for-php/images/heart.svg" alt="love" width="14px"/></a></sub> in Berlin</sub></p>

[ci_badge]: https://github.com/thiagoalessio/tesseract-ocr-for-php/workflows/CI/badge.svg?event=push&branch=main
[ci]: https://github.com/thiagoalessio/tesseract-ocr-for-php/actions?query=workflow%3ACI
[appveyor_badge]: https://ci.appveyor.com/api/projects/status/xwy5ls0798iwcim3/branch/main?svg=true
[appveyor]: https://ci.appveyor.com/project/thiagoalessio/tesseract-ocr-for-php/branch/main
[codacy_badge]: https://app.codacy.com/project/badge/Grade/a81aa10012874f23a57df5b492d835f2
[codacy]: https://www.codacy.com/gh/thiagoalessio/tesseract-ocr-for-php/dashboard
[test_coverage_badge]: https://codecov.io/gh/thiagoalessio/tesseract-ocr-for-php/branch/main/graph/badge.svg?token=Y0VnrqiSIf
[test_coverage]: https://codecov.io/gh/thiagoalessio/tesseract-ocr-for-php
[stable_version_badge]: https://img.shields.io/packagist/v/thiagoalessio/tesseract_ocr.svg
[packagist]: https://packagist.org/packages/thiagoalessio/tesseract_ocr
[total_downloads_badge]: https://img.shields.io/packagist/dt/thiagoalessio/tesseract_ocr.svg
[monthly_downloads_badge]: https://img.shields.io/packagist/dm/thiagoalessio/tesseract_ocr.svg
[Tesseract OCR]: https://github.com/tesseract-ocr/tesseract
[Composer]: http://getcomposer.org/
[windows_icon]: https://thiagoalessio.github.io/tesseract-ocr-for-php/images/windows-18.svg
[macos_icon]: https://thiagoalessio.github.io/tesseract-ocr-for-php/images/apple-18.svg
[tesseract_installation_on_windows]: https://github.com/tesseract-ocr/tesseract/wiki#windows
[Capture2Text]: https://chocolatey.org/packages/capture2text
[Chocolatey]: https://chocolatey.org
[MacPorts]: https://www.macports.org
[Homebrew]: https://brew.sh
[@daijiale]: https://github.com/daijiale
[HOCR]: https://github.com/tesseract-ocr/tesseract/wiki/Command-Line-Usage#hocr-output
[TSV]: https://github.com/tesseract-ocr/tesseract/wiki/Command-Line-Usage#tsv-output-currently-available-in-305-dev-in-master-branch-on-github
[Issue]: https://github.com/thiagoalessio/tesseract-ocr-for-php/issues
[Pull Request]: https://github.com/thiagoalessio/tesseract-ocr-for-php/pulls
[Code of Conduct]: https://github.com/thiagoalessio/tesseract-ocr-for-php/blob/main/.github/CODE_OF_CONDUCT.md
[Contributing]: https://github.com/thiagoalessio/tesseract-ocr-for-php/blob/main/.github/CONTRIBUTING.md
[MIT License]: https://github.com/thiagoalessio/tesseract-ocr-for-php/blob/main/MIT-LICENSE
