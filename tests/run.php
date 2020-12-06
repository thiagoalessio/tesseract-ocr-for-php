<?php namespace thiagoalessio\TesseractOCR\Tests;

require_once __DIR__.'/../vendor/autoload.php';

if (in_array('unit', $argv)) {
	foreach(glob(__DIR__.'/Unit/*Test.php') as $file) require_once $file;
	foreach(glob(__DIR__.'/Unit/**/*Test.php') as $file) require_once $file;
}

if (in_array('e2e', $argv))
	foreach(glob(__DIR__.'/EndToEnd/*.php') as $file) require_once $file;

// setting up code coverage
if (extension_loaded('xdebug')) {
	if (class_exists('\PHP_CodeCoverage')) {
		$coverage = new \PHP_CodeCoverage;
		$coverage->filter()->addDirectoryToWhitelist('./src');
	} else {
		$filter = new \SebastianBergmann\CodeCoverage\Filter;
		$filter->includeDirectory('./src');
		$coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage(
			(new \SebastianBergmann\CodeCoverage\Driver\Selector)->forLineCoverage($filter),
			$filter
		);
	}
	$coverage->start('tests');
}

// running tests
$isTest = function($class) {
	return strstr($class, __NAMESPACE__) && !strstr($class, 'Common');
};
$tests = array_filter(get_declared_classes(), $isTest);
$rc = 0;
foreach ($tests as $test) {
	echo str_replace(__NAMESPACE__.'\\', '', $test), PHP_EOL;

	$results = (new $test)->run();
	foreach ($results as $name => $result) {
		switch ($result['status']) {
			case 'fail':
				$status = "\e[31m✕";
				break;
			case 'pass':
				$status = "\e[32m✓";
				break;
			case 'skip':
				$status = "\e[33m‖";
				break;
		}
		echo "\t{$status} {$name}\e[0m", PHP_EOL;

		if ($result['status'] == 'fail') {
			$rc++;
			echo "\e[35m{$result['msg']}\e[0m", PHP_EOL;
		}
	}
	echo PHP_EOL;
}

// saving coverage results
if (isset($coverage)) {
	$coverage->stop();
	$reportClass = class_exists('\PHP_CodeCoverage_Report_Clover')
		? '\PHP_CodeCoverage_Report_Clover'
		: '\SebastianBergmann\CodeCoverage\Report\Clover';
	$writer = new $reportClass;
	$writer->process($coverage, 'coverage.xml');
}
exit($rc);
