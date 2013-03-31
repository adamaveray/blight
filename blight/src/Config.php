<?php
namespace Blight;

class Config implements \Blight\Interfaces\Config {
	/**
	 * Converts the config data into a text format
	 *
	 * @param array $values	The config data to convert
	 * @return string		The converted string config data
	 */
	public function serialize($values){
		$options	= 0;
		if(false && defined('\JSON_PRETTY_PRINT')){
			$options	= \JSON_PRETTY_PRINT;
		}
		$result	= json_encode($values, $options);
		if($options === 0){
			$result	= $this->prettyPrint($result);
		}
		return $result;
	}

	/**
	 * Converts the config file text into values
	 *
	 * @param string $contents	The contents for the config file
	 * @return array	An associative array of the config data
	 */
	public function unserialize($contents){
		return json_decode($contents, true);
	}

	/**
	 * Formats minified JSON into a more easily human-read layout
	 *
	 * @param string $json	The raw, minified JSON data
	 * @return string		The readable, spaced JSON string
	 */
	protected function prettyPrint($json){
		$output	= '';

		$indent	= 0;
		$indentString		= '    ';
		$separatorString	= ' ';
		$newline		= "\n";
		$prevChar		= '';
		$inQuotes		= false;

		$chars	= str_split($json);
		foreach($chars as $char){
			if($char == '"' && $prevChar != '\\'){
				// Start/end of quoted string
				$inQuotes	= !$inQuotes;

			} elseif(!$inQuotes && ($char == '}' || $char == ']')){
				// End of element
				$indent--;
				$output .= $newline.str_repeat($indentString, $indent);
			}

			// Append character
			$output	.= $char;

			if(!$inQuotes){
				if($char == ':'){
					// End of key
					$output	.= $separatorString;

				} elseif($char == ',' || $char == '{' || $char == '['){
					// Inside new element

					if($char == '{' || $char == '['){
						// Increase indent
						$indent++;
					}

					$output	.= $newline.str_repeat($indentString, $indent);
				}
			}

			$prevChar	= $char;
		}

		return $output;
	}
};
