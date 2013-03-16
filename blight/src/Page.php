<?php
namespace Blight;

class Page implements \Blight\Interfaces\Page {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	protected $title;
	protected $slug;
	protected $date;
	protected $content;
	protected $metadata;

	protected $permalink;

	/**
	 * Initialises a page and processes the metadata contained in the header block
	 *
	 * @param \Blight\Interfaces\Blog $blog
	 * @param string $content	The raw Markdown content for the page
	 * @param string $slug		The page URL slug
	 * @throws \InvalidArgumentException	Page date is invalid
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $content, $slug){
		$this->blog	= $blog;

		$data	= $this->parse_content($content);

		$this->title	= $data['title'];
		$this->content	= $data['content'];
		$this->metadata	= $data['metadata'];

		if($this->has_meta('date')){
			try {
				$this->date	= new \DateTime($this->get_meta('date'));
			} catch(\Exception $e){
				throw new \InvalidArgumentException('Article date invalid');
			}
		}

		$this->slug	= strtolower($slug);
	}

	/**
	 * Processes the post metadata contained in the header block
	 *
	 * @param string $content	The raw Markdown content for the page
	 * @return array			The metadata retrieved from the page
	 * @throws \InvalidArgumentException	The page format is incorrect
	 * @see parse_metadata()
	 */
	protected function parse_content($content){
		$lines	= explode("\n", $content);

		$title	= array_shift($lines);
		if(!preg_match('/^(\={3,})$/', rtrim(array_shift($lines)))){
			throw new \InvalidArgumentException('Article does not meet correct format');
		}

		$metadata	= $this->parse_metadata($lines);

		$content	= trim(implode("\n", $lines));

		return array(
			'title'		=> $title,
			'content'	=> $content,
			'metadata'	=> $metadata
		);
	}

	/**
	 * Processes the page metadata contained in the header block, and strips those lines from
	 * the page content.
	 *
	 * @param array|string &$lines	The lines from the page body
	 * @return array	The metadata contained within the lines
	 */
	protected function parse_metadata(&$lines){
		if(!is_array($lines)){
			$lines	= explode("\n", $lines);
		}

		$metadata	= array();
		while(true){
			$line	= trim(array_shift($lines));
			if($line == ''){
				// End of metadata
				break;
			}

			$line	= array_map('trim', explode(':', $line, 2));
			if(count($line) === 1){
				// Single param
				$line[]	= true;
			}

			$metadata[$this->normalise_meta_name($line[0])]	= $line[1];
		}

		return $metadata;
	}


	/**
	 * @return string	The page title
	 */
	public function get_title(){
		return $this->title;
	}

	/**
	 * @return string	The page slug
	 */
	public function get_slug(){
		return $this->slug;
	}

	/**
	 * @return \DateTime	The page date
	 */
	public function get_date(){
		if(!isset($this->date)){
			return new \DateTime();
		}
		return $this->date;
	}

	/**
	 * @param \DateTime $date	The new date for the post
	 */
	public function set_date(\DateTime $date){
		$this->date	= $date;
	}

	/**
	 * @return string	The page's raw Markdown content
	 */
	public function get_content(){
		return $this->content;
	}

	/**
	 * @return array	The page metadata
	 */
	public function get_metadata(){
		return $this->metadata;
	}

	/**
	 * Gets a metadata value for the page
	 *
	 * @param string $name	The metadata to retrieve
	 * @return mixed|null	The metadata value if set, or null
	 */
	public function get_meta($name){
		$name	= $this->normalise_meta_name($name);

		if(!$this->has_meta($name)){
			return null;
		}

		return $this->metadata[$name];
	}

	/**
	 * Checks if a metadata item is set for the page
	 *
	 * @param string $name	The meta to check if exists
	 * @return bool	If the meta exists
	 */
	public function has_meta($name){
		$name	= $this->normalise_meta_name($name);
		return isset($this->metadata[$name]);
	}

	/**
	 * Converts a metadata name to a standardised format, with punctuation, etc removed
	 *
	 * @param string $name	The metadata name to convert
	 * @return string		The converted name
	 */
	protected function normalise_meta_name($name){
		$clean	= preg_replace('%[^-/+|\w ]%', '', $name);
		$clean	= strtolower(trim($clean, '-'));
		$clean	= preg_replace('/[\/_|+ -]+/', '-', $clean);

		return $clean;
	}

	/**
	 * @return string	The URL to the page
	 */
	public function get_link(){
		return $this->get_permalink();
	}

	/**
	 * @return string	The URL to the page
	 */
	public function get_permalink(){
		if(!isset($this->permalink)){
			$this->permalink	= $this->blog->get_url($this->get_relative_permalink());
		}

		return $this->permalink;
	}

	/**
	 * @return string	The URL to the page without the prefixed site URL
	 */
	public function get_relative_permalink(){
		return $this->slug;
	}
}
