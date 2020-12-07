<?php namespace thiagoalessio\TesseractOCR\Tests\Common;

class TestCase
{
	public function run()
	{
		$results = array();

		if (method_exists($this, 'setUp')) $this->setUp();
		foreach ($this->getTests() as $test) {
			if (method_exists($this, 'beforeEach')) $this->beforeEach();
			try {
				$this->$test();
				$results[$test] = array('status' => 'pass');
			} catch (SkipException $e) {
				$results[$test] = array('status' => 'skip');
			} catch (\Exception $e) {
				$results[$test] = array('status' => 'fail', 'msg' => $e->getMessage());
			}
			if (method_exists($this, 'afterEach')) $this->afterEach();
		}
		if (method_exists($this, 'tearDown')) $this->tearDown();

		return $results;
	}

	protected function getTests()
	{
		$isTest = function ($name) { return preg_match('/^test/', $name); };
		$methods = get_class_methods(get_class($this));
		return array_filter($methods, $isTest);
	}

	protected function assertEquals($expected, $actual)
	{
		if ($expected != $actual) {
			throw new \Exception("\t\tExpected: $expected\n\t\t  Actual: $actual");
		}
	}

	protected function skip()
	{
		throw new SkipException();
	}
}

class SkipException extends \Exception {}
