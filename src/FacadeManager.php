<?php

namespace Cumulati\LaravelFacadeGenerator;

use PhpParser\Comment\Doc;
use Roave\BetterReflection\BetterReflection;

class FacadeManager
{
	public function __construct(
		public string $facade,
		public string $path,
	) {
	}

	public function renderFacade(string $class): void
	{
		$compiled = (new Generator($class))->generate();

		$doc = $this->convertLinesToDocblock($compiled);

		$this->writeDocblock($doc);
	}

	public function createNewFacade(
		string $class,
		?string $accessor = null,
		?int $accessorType = FacadeTemplate::ACCESSOR_TYPE_FQCN
	): void {
		if (file_exists($this->path)) {
			throw new \Exception('cannot create new facade: file exits');
		}

		// create directory if needed and it does not exist
		$pi = pathinfo($this->path);
		if (!empty($pi['dirname']) && !file_exists($pi['dirname'])) {
			mkdir($pi['dirname'], 0755, true);
		}

		if (!$accessor || $accessorType === null) {
			$accessorType = FacadeTemplate::ACCESSOR_TYPE_FQCN;
			$accessor = $class;
		}

		$contents = $this->renderNewFacade($class, $accessor, $accessorType);

		file_put_contents($this->path, $contents);

		$this->renderFacade($class);
	}

	protected function renderNewFacade(
		string $class,
		string $accessor,
		int $accessorType
	): string {
		$template = FacadeTemplate::DEFAULT;
		$nsParts = explode('\\', $this->facade);
		$facade = array_pop($nsParts);
		$ns = implode('\\', $nsParts);
		$use = '';

		if (!$accessor) {
			if ($accessorType === FacadeTemplate::ACCESSOR_TYPE_ALIAS) {
				throw new \LogicException('facade accessor is required for alias types');
			}

			$accessor = '\\' . $class;
		}

		if ($accessorType === FacadeTemplate::ACCESSOR_TYPE_FQCN) {
			$accessorParts = explode('\\', $accessor);
			$accessor = sprintf('%s::class', $accessorParts[count($accessorParts) - 1]);
			$use = sprintf("\nuse %s;", implode('\\', $accessorParts));
		} else {
			$accessor = sprintf('\'%s\'', $accessor);
		}

		return sprintf(
			$template,
			$ns,
			$use,
			$facade,
			$accessor,
		);
	}

	public function convertLinesToDocblock(array $lines): Doc
	{
		$lines = array_map(fn ($l) => rtrim(sprintf(' * %s', $l)), $lines);
		$lines = implode("\n", $lines);

		return new Doc(
			sprintf("/**\n%s\n */", $lines)
		);
	}

	public function writeDocblock(Doc $doc): void
	{
		// remove trailing new line
		// will add later if needed
		$doc = trim($doc->getReformattedText());

		$classInfo = (new BetterReflection())->reflector()->reflectClass($this->facade);
		$current = $classInfo->getDocComment();

		$contents = file_get_contents($this->path);
		if ($current) {
			$contents = str_replace($current, $doc, $contents);
		} else {
			$startLine = $classInfo->getAst()->getAttribute('startLine');
			$lineEnding = PHP_EOL;
			$lines = explode($lineEnding, $contents);
			$linesPrecedingClass = array_slice($lines, 0, $startLine - 1);

			// calculate the number of characters between
			// start of file and class definition
			$charCount = array_reduce(
				$linesPrecedingClass,
				fn ($carry, $line) => $carry + strlen($line),
				count($linesPrecedingClass) * strlen($lineEnding)
			);

			$doc .= PHP_EOL;
			$contents = substr_replace($contents, $doc, $charCount, 0);
		}

		file_put_contents($this->path, $contents);
	}
}
