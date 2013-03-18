<?php
namespace Blight\Interfaces;

interface Utilities {
	/**
	 * @param array $array1	Initial array to merge
	 * @param array $array2
	 * @param array $_
	 * @return mixed
	 */
	public static function array_multi_merge($array1, $array2 = null, $_ = null);
};
