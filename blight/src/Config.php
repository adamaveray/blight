<?php
namespace Blight;

class Config implements \Blight\Interfaces\Config {
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
			$result	= $this->pretty_print($result);
		}
		return $result;
	}

	/**
	 * Formats minified JSON into a more easily human-read layout
	 *
	 * @param string $json	The raw, minified JSON data
	 * @return string		The readable, spaced JSON string
	 */
	protected function pretty_print($json){
		$output	= '';

		$indent	= 0;
		$indent_string		= '    ';
		$separator_string	= ' ';
		$newline		= "\n";
		$prev_char		= '';
		$in_quotes		= false;

		$chars	= str_split($json);
		foreach($chars as $char){
			if($char == '"' && $prev_char != '\\'){
				// Start/end of quoted string
				$in_quotes	= !$in_quotes;

			} elseif(!$in_quotes && ($char == '}' || $char == ']')){
				// End of element
				$indent--;
				$output .= $newline.str_repeat($indent_string, $indent);
			}

			// Append character
			$output	.= $char;

			if(!$in_quotes){
				if($char == ':'){
					// End of key
					$output	.= $separator_string;

				} elseif($char == ',' || $char == '{' || $char == '['){
					// Inside new element

					if($char == '{' || $char == '['){
						// Increase indent
						$indent++;
					}

					$output	.= $newline.str_repeat($indent_string, $indent);
				}
			}

			$prev_char	= $char;
		}

		return $output;
	}
};
