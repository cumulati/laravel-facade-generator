<?php

namespace Cumulati\LaravelFacadeGenerator;

use LogicException;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use Roave\BetterReflection\BetterReflection;
use ReflectionMethod as BaseReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionNamedType;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionUnionType;
use Roave\BetterReflection\Reflection\ReflectionIntersectionType;

class Generator
{
	public function __construct(
		public string $class
	) {
	}

	public function generate(?string $methodName = null): array|string
	{
		$methods = [];
		$classInfo = (new BetterReflection())->reflector()->reflectClass($this->class);

		foreach ($classInfo->getMethods(BaseReflectionMethod::IS_PUBLIC) as $method) {
			if ($methodName && $methodName !== $method->getName()) {
				continue;
			}

			$methods[] = $this->compileMethod($method);
		}

		if (!$methodName) {
			$methods[] = '';
			$methods[] = '@see \\' . $this->class;
			return $methods;
		}

		if (!array_key_exists(0, $methods)) {
			throw new LogicException(sprintf('class method does not exist: %s', $methodName));
		}

		return $methods[0];
	}

	protected function compileMethod(ReflectionMethod $method): string
	{
		$params = array_map(
			fn ($param) => $this->compileMethodParameter($param),
			$method->getParameters()
		);

		$returnType = null;
		if ($method->hasReturnType()) {
			$returnType = $this->compileType($method->getReturnType());
		}

		return sprintf(
			'@method static %s%s(%s)',
			$returnType !== null ? $returnType . ' ' : '',
			$method->getName(),
			implode(', ', $params),
		);
	}

	protected function compileMethodParameter(ReflectionParameter $param): string
	{
		$parts = [];

		if ($param->hasType()) {
			// note, compileType() will include optional param
			$parts[] = $this->compileType($param->getType()) . ' ';
		}

		if ($param->isPassedByReference()) {
			$parts[] = '&';
		}

		if ($param->isVariadic()) {
			$parts[] = '...';
		}

		$parts[] = '$' . $param->getName();

		if ($param->isDefaultValueAvailable()) {
			$parts[] = ' = ';
			$parts[] = $param->isDefaultValueConstant()
				? '\\' . $param->getDefaultValueConstantName()
				: $this->renderDefaultValue($param->getAst()->default);
		}

		return implode('', $parts);
	}

	protected function compileType(
		ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $type
	): string {
		$types = $type instanceof ReflectionNamedType
			? [$type]
			: $type->getTypes();

		$nullableType = false;
		if (count($types) === 2) {
			$nonNullTypes = array_filter($types, fn ($t) => $t->getName() !== 'null');
			$nullableType = count($nonNullTypes) >= 1;

			if ($nullableType) {
				$types = $nonNullTypes;
			}
		}

		$types = array_map(function ($t) {
			if ($t->isBuiltin()) {
				return $t->getName();
			}

			return '\\' . $t->getClass()->getName();
		}, $types);

		// if union or intersection type, get the type glue character
		$glue = gettype($type) !== 'object'
			? null
			: match (get_class($type)) {
				ReflectionUnionType::class => '|',
				ReflectionIntersectionType::class => '&',
				default => null,
			};

		// if we have multiple types, glue them together
		$return = null;
		if ($glue !== null) {
			$return = implode($glue, $types);
		} else {
			$return = $types[0];
		}

		if ($nullableType) {
			$return = '?' . $return;
		}

		return $return;
	}

	protected function renderDefaultValue(Expr $node): mixed
	{
		if ($node->hasAttribute('rawValue')) {
			return $node->getAttribute('rawValue');
		}

		if ($node->getType() === 'Expr_Array') {
			$kind = $node->getAttribute('kind');
			$items = array_map(function ($item) {
				if ($item->key === null) {
					return $item->value->getAttribute('rawValue');
				}

				return sprintf(
					'%s => %s',
					$item->key->getAttribute('rawValue'),
					$item->value->getAttribute('rawValue'),
				);
			}, $node->items);

			$items = implode(', ', $items);

			return sprintf(
				'%s%s%s',
				$kind === Array_::KIND_LONG ? 'array(' : '[',
				$items,
				$kind === Array_::KIND_LONG ? ')' : ']',
			);
		}

		return '';
	}
}
