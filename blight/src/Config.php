<?php
namespace Blight;

class Config implements \Blight\Interfaces\Config {
	public function parse($contents){
		return json_decode($contents, true);
	}

	public function build($values){
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
