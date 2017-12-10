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
    private $executable = '"tesseract"';

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
        $this->image = '"'.addcslashes($image, '\\"').'"';
        return $this;
    }

    /**
     * Executes tesseract command and returns the generated output.
     *
     * @return string
     */
    public function run()
    {
        exec($this->buildCommand(), $output);
        return trim(join(PHP_EOL, $output));
    }

    /**
     * Sets a custom location for the tesseract executable.
     *
     * @param string $executable
     * @return TesseractOCR
     */
    public function executable($executable)
    {
        $this->executable = '"'.addcslashes($executable, '\\"').'"';
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
        if ($this->isShortcut($method)) {
            $class = $this->getShortcutClassName($method);
            $this->options[] = $class::buildOption(...$args);
        }
        elseif ($this->isOption($method)) {
            $class = $this->getOptionClassName($method);
            $this->options[] = new $class(...$args);
        }
        else {
            $var = $this->getConfigVarName($method);
            $value = $args[0];
            $this->options[] = new Option\Config($var, $value);
        }
        return $this;
    }

    /**
     * Builds the tesseract command with all its options.
     *
     * @return string
     */
    public function buildCommand()
    {
        $command = "{$this->executable} {$this->image} stdout";
        foreach ($this->options as $option) {
            $command .= "$option";
        }
        $version = $this->getTesseractVersion();

        if (version_compare($version, '3.03', '>=') &&
            version_compare($version, '3.04', '<')) {
            $command .= ' quiet';
        }

        return $command;
    }

    public function getTesseractVersion() {
        exec("{$this->executable} --version 2>&1", $output);
        return explode(' ', $output[0])[1];
    }

    private function isShortcut($name) {
        return class_exists($this->getShortcutClassName($name));
    }

    private function getShortcutClassName($name) {
        return __NAMESPACE__.'\\Shortcut\\'.ucfirst($name);
    }

    private function isOption($name) {
        return class_exists($this->getOptionClassName($name));
    }

    private function getOptionClassName($name) {
        return __NAMESPACE__.'\\Option\\'.ucfirst($name);
    }

    private function getConfigVarName($name) {
        return strtolower(preg_replace('/([A-Z])+/', '_$1', $name));
    }
}
