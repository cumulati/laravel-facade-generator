<?php

namespace Stubs\Class;

use Exception;

class ExampleClass extends BaseClass
{
	public const COLOR_RED = 'red';

	public function func0(string|OtherClass|null $apples = self::COLOR_RED, ...$fruits): ?OtherClass
	{
		return new OtherClass();
	}

	public function func1(): OtherClass|Exception|null
	{
		return new OtherClass();
	}

	public function func2($banana): ?Exception
	{
		return null;
	}

	public function func3()
	{
		return null;
	}

	public function func4(?string $color)
	{
		return null;
	}

	public function func5($valley = 'beyond'): void
	{
		return;
	}

	public function func6($complexString = 'with\'Quotes'): void
	{
		return;
	}

	public function func7($withKeys = ['a' => 1, 'b' => 2]): void
	{
		return;
	}

	public function func8($withoutKeys = ['a', 'b']): void
	{
		return;
	}

	public function func9($withoutValues = []): void
	{
		return;
	}

	public function func10(&$ref): void
	{
		return;
	}

	public function func11(string &$ref = 'default'): string
	{
		$ref = 'apples';
		return $ref;
	}

	public function func12($null = null): void
	{
		return;
	}

	public function func13(?bool $boolOrNull = null): bool
	{
		return !!$boolOrNull;
	}

	public function func14(bool|null $boolOrNull = null): bool
	{
		return !!$boolOrNull;
	}

	public function func15(bool $bool = true): bool
	{
		return $bool;
	}

	// intentionally with spaces between types
	public function func16(int | string $intOrString): bool
	{
		return false;
	}

	// intentionally with spaces between types
	public function func17(int | null | string $intOrStringOrNull): bool
	{
		return false;
	}

	private function noCanSeeMe(): void
	{
	}
}
