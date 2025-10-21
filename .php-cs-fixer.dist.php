<?php

// ref: https://cs.symfony.com/doc/rules/
// ref: https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/config.rst

$finder = Symfony\Component\Finder\Finder::create()
	->in([
		__DIR__ . '/src',
		__DIR__ . '/tests',
	])
	->name('*.php')
	->ignoreDotFiles(true)
	->ignoreVCS(true);

$config = new PhpCsFixer\Config();

return $config
	->setRules([
		// normal / low risk rules
		'@PSR12' => true,
		'array_indentation' => true,
		'array_syntax' => ['syntax' => 'short'],
		'combine_consecutive_unsets' => true,
		'multiline_whitespace_before_semicolons' => false,
		'single_quote' => true,

		'braces' => [
			'allow_single_line_closure' => true,
		],
		'concat_space' => ['spacing' => 'one'],
		'declare_equal_normalize' => true,
		'function_typehint_space' => true,
		'include' => true,
		'lowercase_cast' => true,
		'ordered_imports' => ['sort_algorithm' => 'length'],
		'no_multiline_whitespace_around_double_arrow' => true,
		'no_spaces_around_offset' => true,
		'no_unused_imports' => true,
		'no_whitespace_before_comma_in_array' => true,
		'no_whitespace_in_blank_line' => true,
		'object_operator_without_whitespace' => true,
		'space_after_semicolon' => true,
		'ternary_operator_spaces' => true,
		'trim_array_spaces' => true,
		'unary_operator_spaces' => true,
		'whitespace_after_comma_in_array' => true,

		//'binary_operator_spaces' => [
		//	 'align_double_arrow' => true,
		//	 'align_equals' => false,
		//],
	])
	->setIndent("\t")
	->setLineEnding("\n")
	->setFinder($finder);
