<?php
namespace Blight;

abstract class Utilities implements \Blight\Interfaces\Utilities {
	/**
	 * Merges two or more multidimensional arrays.
	 *
	 * If a value in one array is an array, but the value in another is not an array,
	 * the non-array value will be added as an item in the merged array:
	 *
	 * 		array('item' => 'string')
	 * 		array('item' => array('value'))
	 *
	 *		array('item' => array('string','value'))
	 *
	 * @param array $array1	Initial array to merge
	 * @param array $array2
	 * @param array $_
	 * @return mixed
	 */
	public static function arrayMultiMerge($array1, $array2 = null, $_ = null){
		$arrays	= func_get_args();
		$base	= array_shift($arrays);
		foreach($arrays as $array){
			foreach($array as $key => $value){
				if(isset($base[$key]) && (is_array($value) || is_array($base[$key]))){
					if(!is_array($base[$key])){
						$base[$key]	= array($base[$key]);
					} elseif(!is_array($value)){
						$value	= array($value);
					}

					$base[$key]	= static::arrayMultiMerge($base[$key], $value);
				} else {
					$base[$key]	= $value;
				}
			}
		}

		return $base;
	}

	/**
	 * Converts a string to a standard slug-compatible format
	 *
	 * @param string $name	The name to convert
	 * @return string		The converted slug
	 */
	public static function convertNameToSlug($name){
		$slug	= preg_replace('%[^-/+|\w ]%', '', $name);
		$slug	= strtolower(trim($slug, '-'));
		$slug	= preg_replace('/[\/_|+ -]+/', '-', $slug);

		return $slug;
	}

};
