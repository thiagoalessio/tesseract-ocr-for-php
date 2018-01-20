<?php namespace thiagoalessio\TesseractOCR\Tests;
require_once('./vendor/autoload.php');

$testSuites = [
	'unit' => [
		'Unit\\CommandTest',
		'Unit\\Option\\ConfigTest',
		'Unit\\Option\\LangTest',
		'Unit\\Option\\PsmTest',
		'Unit\\Option\\TessdataDirTest',
		'Unit\\Option\\UserPatternsTest',
		'Unit\\Option\\UserWordsTest',
		'Unit\\Shortcut\\WhitelistTest',
		'Unit\\TesseractOCRTest',
	],
	'e2e' => [
		'EndToEnd\\ReadmeExamples',
	],
];

// setting up code coverage
if (extension_loaded('xdebug')) {
	$coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage;
	$coverage->filter()->addDirectoryToWhitelist('./src');
	$coverage->start('tests');
}

// running tests
$testResults = [];
foreach ($argv as $suite) {
	if (array_key_exists($suite, $testSuites)) {
		foreach ($testSuites[$suite] as $class) {
			$fullyQualifiedName = __NAMESPACE__."\\$class";
			$testResults[$class] = (new $fullyQualifiedName)->run();
		}
	}
}

// saving coverage results
if (isset($coverage)) {
	$coverage->stop();

	$writer = new \SebastianBergmann\CodeCoverage\Report\Clover;
	$writer->process($coverage, 'coverage.xml');
}

// reporting test results
$rc = 0;
foreach ($testResults as $class => $results) {
	echo $class, PHP_EOL;

	foreach ($results as $name => $result) {
		echo "\t", ($result['failed'] ? "\e[31m✕" : "\e[32m✓"), "{$name}\e[0m", PHP_EOL;

		if ($result['failed']) {
			$rc++;
			echo "\e[35m{$result['msg']}\e[0m", PHP_EOL;
		}
	}

	echo PHP_EOL;
}
exit($rc);
