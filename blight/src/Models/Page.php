<?php
namespace Blight\Models;

class Page implements \Blight\Interfaces\Models\Page {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	protected $title;
	protected $slug;
	protected $date;
	protected $dateUpdated;
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

		$data	= $this->parseContent($content);

		$this->title	= $data['title'];
		$this->content	= $data['content'];
		$this->metadata	= $data['metadata'];

		if($this->hasMeta('date')){
			try {
				$this->date	= new \DateTime($this->getMeta('date'));
			} catch(\Exception $e){
				throw new \InvalidArgumentException('Created date invalid');
			}
		}
		if($this->hasMeta('date-updated')){
			try {
				$this->DateUpdated	= new \DateTime($this->getMeta('date-updated'));
			} catch(\Exception $e){
				throw new \InvalidArgumentException('Modified date invalid');
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
	 * @see parseMetadata()
	 */
	protected function parseContent($content){
		$lines	= explode("\n", $content);

		$title	= array_shift($lines);
		if(!preg_match('/^(\={3,})$/', rtrim(array_shift($lines)))){
			throw new \InvalidArgumentException('Article does not meet correct format');
		}

		$metadata	= $this->parseMetadata($lines);

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
	protected function parseMetadata(&$lines){
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

			$metadata[$this->normaliseMetaName($line[0])]	= $line[1];
		}

		return $metadata;
	}


	/**
	 * @return string	The page title
	 */
	public function getTitle(){
		return $this->title;
	}

	/**
	 * @return string	The page slug
	 */
	public function getSlug(){
		return $this->slug;
	}

	/**
	 * @return \DateTime	The page date
	 */
	public function getDate(){
		if(!isset($this->date)){
			return new \DateTime();
		}
		return $this->date;
	}

	/**
	 * @param \DateTime $date	The new date for the page
	 */
	public function setDate(\DateTime $date){
		$this->date	= $date;
	}

	/**
	 * @return \DateTime	The page modified date
	 */
	public function getDateUpdated(){
		if(!isset($this->DateUpdated)){
			return $this->getDate();
		}

		return $this->DateUpdated;
	}

	/**
	 * @param \DateTime $date	The new modified date for the page
	 */
	public function setDateUpdated(\DateTime $date){
		$this->DateUpdated	= $date;
	}

	/**
	 * @return string	The page's raw Markdown content
	 */
	public function getContent(){
		return $this->content;
	}

	/**
	 * @return array	The page metadata
	 */
	public function getMetadata(){
		return $this->metadata;
	}

	/**
	 * Gets a metadata value for the page
	 *
	 * @param string $name	The metadata to retrieve
	 * @return mixed|null	The metadata value if set, or null
	 */
	public function getMeta($name){
		$name	= $this->normaliseMetaName($name);

		if(!$this->hasMeta($name)){
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
	public function hasMeta($name){
		$name	= $this->normaliseMetaName($name);
		return isset($this->metadata[$name]);
	}

	/**
	 * Converts a metadata name to a standardised format, with punctuation, etc removed
	 *
	 * @param string $name	The metadata name to convert
	 * @return string		The converted name
	 */
	protected function normaliseMetaName($name){
		$clean	= preg_replace('%[^-/+|\w ]%', '', $name);
		$clean	= strtolower(trim($clean, '-'));
		$clean	= preg_replace('/[\/_|+ -]+/', '-', $clean);

		return $clean;
	}

	/**
	 * @return string	The URL to the page
	 */
	public function getLink(){
		return $this->getPermalink();
	}

	/**
	 * @return string	The URL to the page
	 */
	public function getPermalink(){
		if(!isset($this->permalink)){
			$this->permalink	= $this->blog->getURL($this->getRelativePermalink());
		}

		return $this->permalink;
	}

	/**
	 * @return string	The URL to the page without the prefixed site URL
	 */
	public function getRelativePermalink(){
		return $this->slug;
	}
}
