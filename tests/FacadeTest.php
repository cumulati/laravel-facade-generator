<?php

namespace Cumulati\LaravelFacadeGenerator\Tests;

use Stubs\Facade\Facade;
use PhpParser\Comment\Doc;
use Stubs\Facade\BareFacade;
use Stubs\Class\ExampleClass;
use Stubs\Facade\ExampleClassFacade;
use Cumulati\LaravelFacadeGenerator\FacadeManager;
use Cumulati\LaravelFacadeGenerator\FacadeTemplate;

$testFiles = [
	'facade_stubs/originals/Facade.php'     => 'facade_stubs/Facade.php',
	'facade_stubs/originals/BareFacade.php' => 'facade_stubs/BareFacade.php',
];

beforeEach(function () use ($testFiles) {
	foreach ($testFiles as $from => $to) {
		copy($from, $to);
	}
});

afterEach(function () use ($testFiles) {
	foreach ($testFiles as $to) {
		file_exists($to) && unlink($to);
	}

	file_exists('facade_stubs/ExampleClassFacade.php') && unlink('facade_stubs/ExampleClassFacade.php');
});

test('updatesDocblockInPlace', function ($class, $facade, $doc) {
	$path = sprintf('facade_stubs/%s.php', $facade);
	(new FacadeManager($class, $path))
		->writeDocblock(new Doc($doc));

	$contents = file_get_contents($path);
	expect($contents)->toContain(
		sprintf('%s' . PHP_EOL . 'class %s', $doc, $facade)
	);
})->with([
	[Facade::class, 'Facade', '/**' . PHP_EOL . ' * this is a sample' . PHP_EOL . ' * multiline docblock' . PHP_EOL . ' */'],
	[BareFacade::class, 'BareFacade', '/**' . PHP_EOL . ' * sample docblock' . PHP_EOL . ' */'],
]);

test('rendersFacadesInPlace', function ($class, $facade, $rootClass) {
	$path = sprintf('facade_stubs/%s.php', $facade);

	(new FacadeManager($class, $path))
		->renderFacade($rootClass);

	$contents = file_get_contents($path);

	// a simple assertion ensuring we went from no file to file with generated docblock
	expect($contents)->toContain('@see \Stubs\Class\ExampleClass');
})->with([
	[Facade::class, 'Facade', ExampleClass::class],
]);

test('createsNewFacades', function ($class, $facade, $rootClass, $accessor, $accessorType) {
	$path = sprintf('facade_stubs/%s.php', $facade);

	(new FacadeManager($class, $path))
		->renderFacade($rootClass, $accessor, $accessorType);

	$expectedAccessorFormatString = $accessorType === FacadeTemplate::ACCESSOR_TYPE_FQCN
		? '::class;'
		: 'return \'%s\';';

	$expectedAccessorString = sprintf($expectedAccessorFormatString, $accessor);

	$contents = file_get_contents($path);
	expect($contents)->toContain(
		sprintf('class %s extends Facade', $facade),
		$expectedAccessorString
	);
})->with([
	[ExampleClassFacade::class, 'ExampleClassFacade', ExampleClass::class, ExampleClass::class, FacadeTemplate::ACCESSOR_TYPE_FQCN],
	[ExampleClassFacade::class, 'ExampleClassFacade', ExampleClass::class, 'ExampleClass', FacadeTemplate::ACCESSOR_TYPE_ALIAS],
]);

test('rendersFacades', function ($class, $facade, $rootClass) {
	$path = sprintf('facade_stubs/%s.php', $facade);

	(new FacadeManager($class, $path))
		->renderFacade($rootClass);

	$contents = file_get_contents($path);

	// a simple assertion ensuring we went from no file to file with generated docblock
	expect($contents)->toContain('@see \Stubs\Class\ExampleClass');
	expect($contents)->toEndWith("\n");
})->with([
	[ExampleClassFacade::class, 'ExampleClassFacade', ExampleClass::class],
]);

test('rendersFacadesWithoutWritingMethods', function ($class, $facade, $rootClass) {
	$path = sprintf('facade_stubs/%s.php', $facade);

	(new FacadeManager($class, $path))
		->renderFacade($rootClass, writeMethods: false);

	$contents = file_get_contents($path);

	expect($contents)->not->toContain('@method');
	expect($contents)->toContain('@see \Stubs\Class\ExampleClass');
	expect($contents)->toEndWith("\n");
})->with([
	[ExampleClassFacade::class, 'ExampleClassFacade', ExampleClass::class],
]);
