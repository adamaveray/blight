<?php
namespace Blight\Interfaces;

interface Utilities {
	/**
	 * @param array $array1	Initial array to merge
	 * @param array $array2
	 * @param array $_
	 * @return mixed
	 */
	public static function arrayMultiMerge($array1, $array2 = null, $_ = null);

	/**
	 * @param string $name	The name to convert
	 * @return string		The converted slug
	 */
	public static function convertStringToSlug($name);
};
