<?php
/**
 * A wrapper to work with TesseractOCR inside PHP.
 */
class TesseractOCR
{
    /**
     * Path to the image to be recognized.
     *
     * @var string
     */
    private $image;

    /**
     * Path to tesseract executable.
     * Default value assumes it is present in the $PATH.
     *
     * @var string
     */
    private $executable = 'tesseract';

    /**
     * Command options.
     *
     * @var string
     */
    private $options = '';

    /**
     * List of tesseract configuration variables.
     *
     * @var array
     */
    private $configs = [];

    /**
     * Class constructor.
     *
     * @param string $image
     * @return TesseractOCR
     */
    public function __construct($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Executes tesseract command and returns the generated output.
     *
     * @return string
     */
    public function run()
    {
        return trim(`{$this->buildCommand()}`);
    }

    /**
     * Sets a custom location for the tesseract executable.
     *
     * @param string $executable
     * @return TesseractOCR
     */
    public function executable($executable)
    {
        $this->executable = $executable;
        return $this;
    }

    /**
     * Sets the language(s).
     *
     * @param string ...$languages
     * @return TesseractOCR
     */
    public function lang()
    {
        $this->options .= ' -l '.join('+', func_get_args());
        return $this;
    }

    /**
     * Sets the Page Segmentation Mode value.
     *
     * @param integer $psm
     * @return TesseractOCR
     */
    public function psm($psm)
    {
        $this->options .= ' -psm '.$psm;
        return $this;
    }

    /**
     * Sets a tesseract configuration value.
     *
     * @param string $key
     * @param string $value
     * @return TesseractOCR
     */
    public function config($key, $value)
    {
        $this->configs[$key] = $value;
        return $this;
    }

    /**
     * Shortcut to set tessedit_char_whitelist values in a more convenient way.
     * Example:
     *
     *     (new WrapTesseractOCR('image.png'))
     *         ->whitelist(range(0, 9), range('A', 'F'), '-_@')
     *
     * @param mixed ...$charlists
     * @return TesseractOCR
     */
    public function whitelist()
    {
        $concatenate = function($carry, $item) {
            return $carry.join('', (array)$item);
        };
        $whitelist = array_reduce(func_get_args(), $concatenate, '');
        $this->config('tessedit_char_whitelist', $whitelist);
        return $this;
    }

    /**
     * @DEPRECATED now it always redirects STDERR to /dev/null,
     * this option will be removed on the next major version, just keeping an
     * empty method here for backwards compatibility.
     */
    public function suppressErrors()
    {
        return $this;
    }

    /**
     * Catch all undeclared method invocations
     * and threat them as command options.
     *
     * @return $this
     */
    public function __call($method, $args)
    {
        $option = strtolower(preg_replace('/([A-Z])+/', '-$1', $method));
        $value = $args[0];
        $this->options .= ' --'.$option.' "'.addcslashes($value, '\\"').'"';
        return $this;
    }

    /**
     * Builds the tesseract command with all its options.
     *
     * @return string
     */
    public function buildCommand()
    {
        $cmd = '"'.addcslashes($this->executable, '\\"').'" ';
        $cmd .= '"'.addcslashes($this->image, '\\"').'" stdout';
        $cmd .= $this->options.$this->buildConfigurationsParam();
        return $cmd;
    }

    /**
     * Return tesseract command line arguments for every custom configuration.
     *
     * @return string
     */
    private function buildConfigurationsParam()
    {
        $buildParam = function($config, $value) {
            return ' -c "'.addcslashes("$config=$value", '\\"').'"';
        };
        return join('', array_map(
            $buildParam,
            array_keys($this->configs),
            array_values($this->configs)
        ));
    }
}
