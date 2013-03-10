<?php
namespace Blight;

/**
 * Provides utility helper methods for manipulating blocks of text
 */
class TextProcessor implements \Blight\Interfaces\TextProcessor {
	protected $blog;

	/** @var \phpTypograhy */
	protected $typography;
	/** @var \Markdown */
	protected $markdown;
	/** @var \TruncateHTML */
	protected $truncator;

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
	 * Truncates a block of HTML to a specified length.
	 *
	 * @param string $html	The HTML to truncate
	 * @param int $length	The maximum length of text to return. If the given HTML is shorter than this length,
	 * 						no truncation will take place.
	 * @param string $ending	Characters to be appended if the string is truncated
	 * @return string	The truncated string
	 */
	public function truncate_html($html, $length = 100, $ending = '...'){
		return $this->get_truncator()->truncate($html, $length, $ending);
	}


	/**
	 * @return \Markdown	The Markdown parsing instance
	 */
	protected function get_markdown(){
		if(!isset($this->markdown)){
			$this->markdown	= new \Markdown();
		}

		return $this->markdown;
	}

	/**
	 * @return \phpTypograhy	The phpTypography instance
	 */
	protected function get_typography(){
		if(!isset($this->typography)){
			$this->typography	= new \phpTypography();
		}

		return $this->typography;
	}

	/**
	 * @return \TruncateHTML	The TruncateHTML instance
	 */
	protected function get_truncator(){
		if(!isset($this->truncator)){
			$this->truncator	= new \TruncateHTML();
		}

		return $this->truncator;
	}
};