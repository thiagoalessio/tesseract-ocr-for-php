<?php
/**
 * The only purpose of the following unit tests is to verify if the tesseract
 * command gets correctly built with its options.
 * The commands are not actually being executed during the tests.
 */

use PHPUnit\Framework\TestCase;

class UnitTests extends TestCase
{
    /**
     * Simplest tesseract command, with no additional options.
     */
    public function testSimplestCommand()
    {
        $expected = "'tesseract' 'image.png' stdout quiet";

        $actual = (new TesseractOCR('image.png'))
            ->buildCommand();

        $this->assertEquals($expected, $actual);
    }

    /**
     * One should be able to specify the location of the tesseract executable,
     * if by any reason it is not present in the $PATH.
     * It should be surrounded by single quotes, in case there are spaces on the path,
     * like 'C:/Program Files (x86)/Tesseract-OCR/tesseract.exe' for example.
     */
    public function testDefiningLocationOfTesseractExecutable()
    {
        $expected = "'/path/to/tesseract' 'image.png' stdout quiet";

        $actual = (new TesseractOCR('image.png'))
            ->executable('/path/to/tesseract')
            ->buildCommand();

        $this->assertEquals($expected, $actual);
    }

    /**
     * When a custom tessdata directory is specified, a '--tessdata-dir' option
     * should be appended to the command with its correspondent value.
     */
    public function testDefiningLocationOfTessdataDir()
    {
        $expected = "'tesseract' 'image.png' stdout --tessdata-dir /path quiet";

        $actual = (new TesseractOCR('image.png'))
            ->tessdataDir('/path')
            ->buildCommand();

        $this->assertEquals($expected, $actual);
    }

    /**
     * When a custom user words file is specified, a '--user-words' option
     * should be appended to the command with its correspondent value.
     * Example of a user words file:
     *
     *     $ cat /path/to/user-words.txt
     *     foo
     *     bar
     */
    public function testDefiningLocationOfUserWords()
    {
        $expected = "'tesseract' 'image.png' stdout"
            .' --user-words /path/to/user-words.txt quiet';

        $actual = (new TesseractOCR('image.png'))
            ->userWords('/path/to/user-words.txt')
            ->buildCommand();

        $this->assertEquals($expected, $actual);
    }

    /**
     * When a custom user patterns file is specified, a '--user-patterns'
     * option should be appended to the command with its correspondent value.
     * Example of a user patterns file:
     *
     *     $ cat /path/to/user-patterns.txt
     *     1-\d\d\d-GOOG-441
     *     www.\n\\\*.com
     */
    public function testDefiningLocationOfUserPatterns()
    {
        $expected = "'tesseract' 'image.png' stdout"
            .' --user-patterns /path/to/user-patterns.txt quiet';

        $actual = (new TesseractOCR('image.png'))
            ->userPatterns('/path/to/user-patterns.txt')
            ->buildCommand();

        $this->assertEquals($expected, $actual);
    }

    /**
     * When a language is provided, the '-l' option should be appended to the
     * command with its correspondent value.
     */
    public function testLanguageOption()
    {
        $expected = "'tesseract' 'image.png' stdout -l deu quiet";

        $actual = (new TesseractOCR('image.png'))
            ->lang('deu')
            ->buildCommand();

        $this->assertEquals($expected, $actual);
    }

    /**
     * Some sugar when providing multiple languages, because passing them all
     * as one single parameter doesn't look very nice:
     *
     *     (new TesseractOCR('img'))->lang('eng+deu+jpn');
     */
    public function testLanguageOptionForMultipleLanguages()
    {
        $expected = "'tesseract' 'image.png' stdout -l eng+deu+jpn quiet";

        $actual = (new TesseractOCR('image.png'))
            ->lang('eng', 'deu', 'jpn')
            ->buildCommand();

        $this->assertEquals($expected, $actual);
    }

    /**
     * When a page segmentation mode is provided, the '-psm' option should be
     * appended to the command with its correspondent value.
     */
    public function testPsmOption()
    {
        $expected = "'tesseract' 'image.png' stdout -psm 8 quiet";

        $actual = (new TesseractOCR('image.png'))
            ->psm(8)
            ->buildCommand();

        $this->assertEquals($expected, $actual);
    }

    /**
     * The '-psm' option should be present even if it has a "falsy" value.
     */
    public function testPsmOptionWithValueZero()
    {
        $expected = "'tesseract' 'image.png' stdout -psm 0 quiet";

        $actual = (new TesseractOCR('image.png'))
            ->psm(0)
            ->buildCommand();

        $this->assertEquals($expected, $actual);
    }

    /**
     * When a control parameter is provided, a '-c' option should be appended
     * to the command with its correspondent pair configvar=value.
     * Multiple control parameters are allowed.
     */
    public function testConfigOption()
    {
        $expected = "'tesseract' 'image.png' stdout"
            ." -c 'tessedit_create_pdf=1'"
            ." -c 'load_system_dawg=F' quiet";

        $actual = (new TesseractOCR('image.png'))
            ->config('tessedit_create_pdf', '1')
            ->config('load_system_dawg', 'F')
            ->buildCommand();

        $this->assertEquals($expected, $actual);
    }

    /**
     * Some sugar to make char whitelisting pleasurable to use and read.
     */
    public function testWhitelistSettingShortcutWithMultipleRanges()
    {
        $expected = "'tesseract' 'image.png' stdout"
            ." -c 'tessedit_char_whitelist=0123456789ABCDEF-_@' quiet";

        $actual = (new TesseractOCR('image.png'))
            ->whitelist(range(0, 9), range('A', 'F'), '-_@')
            ->buildCommand();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @DEPRECATED The quiet mode is activated.
     */
    public function testQuietMode()
    {
        $expected = "'tesseract' 'image.png' stdout quiet";

        $actual = (new TesseractOCR('image.png'))
            ->quietMode(true)
            ->buildCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testSuppressErrorsMode()
    {
        $expected = "'tesseract' 'image.png' stdout quiet";

        $actual = (new TesseractOCR('image.png'))
            ->quietMode(true)
            ->suppressErrors()
            ->buildCommand();

        $this->assertEquals($expected, $actual);
    }
}
