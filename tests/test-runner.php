<?php
define('DS', DIRECTORY_SEPARATOR);

require_once(join(DS, [__DIR__, '..', 'vendor', 'autoload.php']));
array_shift($argv);

// setting up coverage
if (extension_loaded('xdebug')) {
	$coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage;
	$coverage->filter()->addDirectoryToWhitelist(join(DS, [__DIR__, '..', 'src']));
}

// running test suites
$results = [];
if (isset($coverage)) $coverage->start('tests');
foreach ($argv as $suite) {
	$className = 'thiagoalessio\\TesseractOCR\\Tests\\'.ucfirst($suite).'Tests';
	$results[$suite] = (new $className)->run();
}
if (isset($coverage)) $coverage->stop();

// saving coverage results
if (isset($coverage)) {
	$writer = new \SebastianBergmann\CodeCoverage\Report\Clover;
	$writer->process($coverage, 'coverage.xml');
}

// reporting test results on console
$rc = 0;
foreach ($results as $suite => $tests) {
	echo $suite, PHP_EOL;
	foreach ($tests as $name => $status) {
		echo "\t\e[3".($status['failed'] ? "1m✕" : "2m✓")." $name\e[0m".PHP_EOL;

		if ($status['failed']) {
			$rc++;
			echo "\e[35m", $status['msg'], "\e[0m", PHP_EOL;
		}
	}
	echo PHP_EOL;
}
exit($rc);
