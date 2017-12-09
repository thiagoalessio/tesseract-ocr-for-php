<?php namespace thiagoalessio\TesseractOCR;

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
     * @var array
     */
    private $options = [];

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
     * Catch all undeclared method invocations
     * and threat them as command options.
     *
     * @return $this
     */
    public function __call($method, $args)
    {
        $className = __NAMESPACE__.'\\Option\\'.ucfirst($method);
        if (class_exists($className)) {
            $this->options[] = new $className(...$args);
            return $this;
        }

        $option = strtolower(preg_replace('/([A-Z])+/', '_$1', $method));
        $value = $args[0];
        $this->options[] = new Option\Config($option, $value);
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
        foreach ($this->options as $opt) $cmd .= "$opt";
        return $cmd;
    }
}
