<?php
namespace Blight;

/**
 * Provides utility helper methods for manipulating blocks of text
 */
class TextProcessor implements \Blight\Interfaces\TextProcessor {
	protected $blog;

	/** @var \phpTypograhy */
	protected $typography;
	/** @var \dflydev\markdown\MarkdownExtraParser */
	protected $markdown;

	/**
	 * Initialises the processor
	 *
	 * @param \Blight\Interfaces\Blog $blog
	 */
	public function __construct(\Blight\Interfaces\Blog $blog){
		$this->blog	= $blog;
	}

	/**
	 * Processes the given text by applying a number of typographical filters
	 *
	 * @param string $raw	The text to process
	 * @param array|null $filters	An array of filters to enable or disable
	 *
	 * 		'markdown'
	 *
	 * 		'typography'
	 *
	 * @return string	The processed text
	 * @see process_markdown()
	 * @see process_typography()
	 */
	public function process($raw, $filters = null){
		if(!is_array($filters)){
			$filters	= array(
				'markdown'		=> true,
				'typography'	=> true
			);
		}

		$filters	= array_merge(array(
			'markdown'		=> false,
			'typography'	=> false
		), $filters);

		$output	= $raw;

		if($filters['markdown']){
			$output	= $this->process_markdown($output);
		}
		if($filters['typography']){
			$output	= $this->process_typography($output);
		}

		return $output;
	}

	/**
	 * Converts a block of Markdown to HTML
	 *
	 * @param string $raw	The raw Markdown
	 * @return string		The processed HTML
	 */
	public function process_markdown($raw){
		return $this->get_markdown()->transform($raw);
	}

	/**
	 * Applies typography filters to a block of HTML
	 *
	 * @param string $html	The HTML to process
	 * @return string		The processed HTML
	 */
	public function process_typography($html){
		$errors	= error_reporting(0);
		$result	= $this->get_typography()->process($html);
		error_reporting($errors);

		return $result;
	}

	/**
	 * Shortens a given block of text, preserving HTML tags and whole words
	 *
	 * @param string $html	The HTML to truncate
	 * @param int $length	The maximum length of text to return. If the given HTML is shorter than this length,
	 * 						no truncation will take place.
	 * @param string $ending	Characters to be appended if the string is truncated
	 * @param boolean $split_words		Whether to truncate text mid-word
	 * @param boolean $handle_html	If true, HTML tags would be handled correctly
	 *
	 * @return string	The truncated text
	 */
	public function truncate_html($html, $length = 100, $ending = '...', $split_words = false, $handle_html = true){
		if(!$handle_html){
			// Ignore HTML tags
			if(strlen($html) <= $length){
				// No need to truncate
				return $html;
			} else {
				// Truncate text
				$output	= substr($html, 0, $length - strlen($ending));
			}
		} else {
			if(strlen(strip_tags($html)) <= $length){
				// No need to truncate
				return $html;
			}

			// Split HTML tags to scannable lines
			preg_match_all('/(<.+?>)?([^<>]*)/s', $html, $lines, \PREG_SET_ORDER);

			$total_length	= strlen($ending);
			$open_tags		= array();
			$output			= '';
			foreach($lines as $line_matchings){
				if (!empty($line_matchings[1])) {
					// Has HTML tag
					if(preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])){
						// Empty element - ignore
					} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						// Closing tag - delete from open tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if($pos !== false){
							unset($open_tags[$pos]);
						}

					} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						// Opening tag - add to open tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}

					// Add tag to truncated text
					$output .= $line_matchings[1];
				}

				// Caclulate length of plain text in line
				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if(($total_length + $content_length) > $length){
					// Remaining characters
					$left = $length - $total_length;

					$entities_length = 0;
					// Find HTML entities
					if(preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, \PREG_OFFSET_CAPTURE)){
						// Calculate real length of all entities whithin legal range
						foreach($entities[0] as $entity){
							if($entity[1]+1-$entities_length > $left){
								continue;
							}

							$left--;
							$entities_length += strlen($entity[0]);
						}
					}

					$output .= substr($line_matchings[2], 0, $left+$entities_length);
					// maximum lenght is reached, so get off the loop
					break;

				} else {
					$output .= $line_matchings[2];
					$total_length += $content_length;
				}
				// if the maximum length is reached, get off the loop
				if($total_length>= $length) {
					break;
				}
			}
		}

		if(!$split_words){
			// Don't split words
			$spacepos = strrpos($output, ' ');
			if(isset($spacepos)){
				// Cut text at last occurance of space
				$output = substr($output, 0, $spacepos);
			}
		}

		// Append ending string
		$output .= $ending;
		if($handle_html){
			// Close remaining HTML tags
			foreach($open_tags as $tag){
				$output .= '</'.$tag.'>';
			}
		}

		return $output;
	}

	/**
	 * @return \dflydev\markdown\MarkdownExtraParser	The Markdown parsing instance
	 */
	protected function get_markdown(){
		if(!isset($this->markdown)){
			$this->markdown	= new \dflydev\markdown\MarkdownExtraParser();
		}

		return $this->markdown;
	}

	/**
	 * @return \PHPTypography\PHPTypograhy	The phpTypography instance
	 */
	protected function get_typography(){
		if(!isset($this->typography)){
			$this->typography	= new \PHPTypography\PHPTypography();
		}

		return $this->typography;
	}
};