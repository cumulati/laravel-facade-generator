<?php

namespace Cumulati\LaravelFacadeGenerator\Tests;

use Stubs\Class\ExampleClass;
use Cumulati\LaravelFacadeGenerator\Generator;

$methods = [
	['func0', '@method static ?\Stubs\Class\OtherClass func0(string|\Stubs\Class\OtherClass|null $apples = \Stubs\Class\ExampleClass::COLOR_RED, ...$fruits)'],
	['func1', '@method static \Stubs\Class\OtherClass|\Exception|null func1()'],
	['func2', '@method static ?\Exception func2($banana)'],
	['func3', '@method static func3()'],
	['func4', '@method static func4(?string $color)'],
	['func5', '@method static void func5($valley = \'beyond\')'],
	['func6', '@method static void func6($complexString = \'with\\\'Quotes\')'],
	['func7', '@method static void func7($withKeys = [\'a\' => 1, \'b\' => 2])'],
	['func8', '@method static void func8($withoutKeys = [\'a\', \'b\'])'],
	['func9', '@method static void func9($withoutValues = [])'],
	['func10', '@method static void func10(&$ref)'],
	['func11', '@method static string func11(string &$ref = \'default\')'],
	['func12', '@method static void func12($null = null)'],
	['func13', '@method static bool func13(?bool $boolOrNull = null)'],
	['func14', '@method static bool func14(?bool $boolOrNull = null)'],
	['func15', '@method static bool func15(bool $bool = true)'],
	['base0', '@method static int base0()'],
];

test('dataset', function ($method, $docString) {
	$compiled = (new Generator(ExampleClass::class))->generate($method);

	expect($compiled)->toEqual($docString);
})->with($methods);

test('generatesAllMethods', function () use ($methods) {
	$compiled = (new Generator(ExampleClass::class))->generate();

	$expectedLines = [
		...array_column($methods, 1),
		'',
		'@see \Stubs\Class\ExampleClass',
	];

	expect($compiled)->toEqualCanonicalizing($expectedLines);
});

test('onlyGeneratesPublicMethods', function () {
	$compiled = (new Generator(ExampleClass::class))->generate();
	expect($compiled)->not->toContain('noCanSeeMe');
});
