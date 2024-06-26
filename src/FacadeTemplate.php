<?php

namespace Cumulati\LaravelFacadeGenerator;

class FacadeTemplate
{
	public const ACCESSOR_TYPE_FQCN = 1;
	public const ACCESSOR_TYPE_ALIAS = 2;
	public const GENERATED_HEADER = [
		'THIS FILE IS AUTO GENERATED by laravel-facade-generator.',
		'Any changes will be overwritten on when next generation is ran.',
	];

	public const DEFAULT = <<<EOF
<?php

namespace %s;

use Illuminate\Support\Facades\Facade;

class %s extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return %s;
	}
}

EOF;
}
