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

	protected $cache;

	/**
	 * Initialises the processor
	 *
	 * @param \Blight\Interfaces\Blog $blog
	 */
	public function __construct(\Blight\Interfaces\Blog $blog){
		$this->blog	= $blog;

		$this->cache	= array();
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
	 * @see processMarkdown()
	 * @see processTypography()
	 */
	public function process($raw, $filters = null){
		$cacheKey	= array(
			'src'		=> $raw,
			'filters'	=> $filters
		);
		$result	= $this->getCachedOutput($cacheKey);
		if($result){
			return $result;
		}

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
			$output	= $this->processMarkdown($output);
		}
		if($filters['typography']){
			$output	= $this->processTypography($output);
		}

		$this->setCachedOutput($cacheKey, $output);

		return $output;
	}

	/**
	 * Converts a block of Markdown to HTML
	 *
	 * @param string $raw	The raw Markdown
	 * @return string		The processed HTML
	 */
	public function processMarkdown($raw){
		return $this->getMarkdown()->transform($raw);
	}

	/**
	 * Applies typography filters to a block of HTML
	 *
	 * @param string $html	The HTML to process
	 * @return string		The processed HTML
	 */
	public function processTypography($html){
		$errors	= error_reporting(0);
		$this->getTypography()->set_hyphenation($this->blog->get('output.generate_hypenation', true));
		$result	= $this->getTypography()->process($html);
		error_reporting($errors);

		$this->blog->doHook('processTypography', array(
			'html'	=> &$result
		));

		return $result;
	}

	/**
	 * Shortens a given block of text, preserving HTML tags and whole words
	 *
	 * @param string $html	The HTML to truncate
	 * @param int $length	The maximum length of text to return. If the given HTML is shorter than this length,
	 * 						no truncation will take place.
	 * @param string $ending	Characters to be appended if the string is truncated
	 * @param boolean $splitWords		Whether to truncate text mid-word
	 * @param boolean $handleHTML	If true, HTML tags would be handled correctly
	 *
	 * @return string	The truncated text
	 */
	public function truncateHTML($html, $length = 100, $ending = '…', $splitWords = false, $handleHTML = true){
		if(!$handleHTML){
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

			$totalLength	= strlen($ending);
			$openTags		= array();
			$output			= '';
			foreach($lines as $lineMatchings){
				if (!empty($lineMatchings[1])) {
					// Has HTML tag
					if(preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $lineMatchings[1])){
						// Empty element - ignore
					} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $lineMatchings[1], $tagMatchings)) {
						// Closing tag - delete from open tags list
						$pos = array_search($tagMatchings[1], $openTags);
						if($pos !== false){
							unset($openTags[$pos]);
						}

					} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $lineMatchings[1], $tagMatchings)) {
						// Opening tag - add to open tags list
						array_unshift($openTags, strtolower($tagMatchings[1]));
					}

					// Add tag to truncated text
					$output .= $lineMatchings[1];
				}

				// Caclulate length of plain text in line
				$contentLength = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $lineMatchings[2]));
				if(($totalLength + $contentLength) > $length){
					// Remaining characters
					$left = $length - $totalLength;

					$entitiesLength = 0;
					// Find HTML entities
					if(preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $lineMatchings[2], $entities, \PREG_OFFSET_CAPTURE)){
						// Calculate real length of all entities whithin legal range
						foreach($entities[0] as $entity){
							if($entity[1]+1-$entitiesLength > $left){
								continue;
							}

							$left--;
							$entitiesLength += strlen($entity[0]);
						}
					}

					$output .= substr($lineMatchings[2], 0, $left+$entitiesLength);
					// maximum lenght is reached, so get off the loop
					break;

				} else {
					$output .= $lineMatchings[2];
					$totalLength += $contentLength;
				}
				// if the maximum length is reached, get off the loop
				if($totalLength >= $length) {
					break;
				}
			}
		}

		if(!$splitWords){
			// Don't split words
			$spacePosition = strrpos($output, ' ');
			if(isset($spacePosition)){
				// Cut text at last occurance of space
				$output = substr($output, 0, $spacePosition);
			}
		}

		// Append ending string
		$output .= $ending;
		if($handleHTML){
			// Close remaining HTML tags
			foreach($openTags as $tag){
				$output .= '</'.$tag.'>';
			}
		}

		return $output;
	}

	/**
	 * Minifies the provided HTML by removing whitespace, etc
	 *
	 * @param string $html	The raw HTML to minify
	 * @return string		The minified HTML
	 */
	public function minifyHTML($html){
		return \Minify_HTML::minify($html);
	}


	/**
	 * @return \dflydev\markdown\MarkdownExtraParser	The Markdown parsing instance
	 */
	protected function getMarkdown(){
		if(!isset($this->markdown)){
			$this->markdown	= new \Michelf\MarkdownExtra();
		}

		return $this->markdown;
	}

	/**
	 * @return \PHPTypography\PHPTypograhy	The PHPTypography instance
	 */
	protected function getTypography(){
		if(!isset($this->typography)){
			$this->typography	= new \PHPTypography\PHPTypography();
		}

		return $this->typography;
	}


	/**
	 * @param mixed $key	The unique key for the cached value
	 * @return string|null	The pre-rendered value
	 */
	protected function getCachedOutput($key){
		$key	= md5(print_r($key, true));
		return isset($this->cache[$key]) ? $this->cache[$key] : null;
	}

	/**
	 * @param mixed $key	The unique key for the value to be cached
	 * @param string|null $value	The rendered value
	 */
	protected function setCachedOutput($key, $value){
		$this->cache[md5(print_r($key, true))]	= $value;
	}


	/**
	 * @param string $slug	The string to convert
	 * @return string		The converted string
	 *
	 * @see \Blight\Interfaces\Utilities::convertStringToSlug
	 */
	public function convertStringToSlug($string){
		return \Blight\Utilities::convertStringToSlug($string);
	}
};