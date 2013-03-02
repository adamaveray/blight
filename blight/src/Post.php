<?php
namespace Blight;

/**
 * A blog post
 */
class Post {
	protected $blog;

	protected $title;
	protected $slug;
	protected $date;
	protected $content;
	protected $metadata;
	protected $tags;
	protected $category;

	protected $link;
	protected $permalink;

	/**
	 * Initialises a post and processes the metadata contained in the header block
	 *
	 * @param Blog $blog
	 * @param string $content	The raw Markdown content for the post
	 * @param string $slug		The post URL slug
	 * @throws \InvalidArgumentException	Article date is invalid
	 */
	public function __construct(Blog $blog, $content, $slug){
		$this->blog	= $blog;

		$data	= $this->parse_content($content);

		$this->title	= $data['title'];
		$this->content	= $data['content'];
		$this->metadata	= $data['metadata'];

		try {
			if(!$this->has_meta('date')){
				throw new \Exception();
			}

			$this->date	= new \DateTime($this->get_meta('date'));

		} catch(\Exception $e){
			throw new \InvalidArgumentException('Article date invalid');
		}

		$this->slug	= strtolower($slug);
	}

	/**
	 * Processes the post metadata contained in the header block
	 *
	 * @param string $content	The raw Markdown content for the post
	 * @return array			The metadata retrieved from the post
	 * @throws \InvalidArgumentException	The article format is incorrect
	 * @see parse_metadata()
	 */
	protected function parse_content($content){
		$lines	= explode($this->blog->get_eol(), $content);

		$title	= array_shift($lines);
		if(!preg_match('/^(\={3,})$/', rtrim(array_shift($lines)))){
			throw new \InvalidArgumentException('Article does not meet correct format');
		}

		$metadata	= $this->parse_metadata($lines);

		$content	= trim(implode($this->blog->get_eol(), $lines));

		return array(
			'title'		=> $title,
			'content'	=> $content,
			'metadata'	=> $metadata
		);
	}

	/**
	 * Processes the post metadata contained in the header block, and strips those lines from
	 * the post content.
	 *
	 * @param array|string &$lines	The lines from the post body
	 * @return array	The metadata contained within the lines
	 */
	protected function parse_metadata(&$lines){
		if(!is_array($lines)){
			$lines	= explode($this->blog->get_eol(), $lines);
		}

		$metadata	= array();
		while(true){
			$line	= trim(array_shift($lines));
			if($line == ''){
				// End of metadata
				break;
			}

			$line	= array_map('trim', explode(':', $line, 2));
			if(count($line) != 2){
				// Unknown metadata
				continue;
			}

			$metadata[$this->normalise_meta_name($line[0])]	= $line[1];
		}

		return $metadata;
	}


	/**
	 * @param bool $raw	Whether to prepend any additional linkblog glyphs to the title
	 * @return string	The post title
	 */
	public function get_title($raw = false){
		if(!$raw){
			$is_linkblog	= $this->blog->is_linkblog();
			$is_linkpost	= $this->is_linked();

			$prepend	= '';
			if($is_linkblog && !$is_linkpost){
				// Unlinked post - prepend glyph
				$prepend	= $this->blog->get('post_character', 'linkblog', '★').' ';

			} elseif(!$is_linkblog && $is_linkpost){
				// Linked post - prepend arrow
				$prepend	= $this->blog->get('link_character', 'linkblog', '→').' ';
			}
		}

		return $prepend.$this->title;
	}

	/**
	 * @return string	The post slug
	 */
	public function get_slug(){
		return $this->slug;
	}

	/**
	 * @return \DateTime	The post date
	 */
	public function get_date(){
		return $this->date;
	}

	/**
	 * @return string	The post's raw Markdown content
	 */
	public function get_content(){
		return $this->content;
	}

	/**
	 * @return array	The post metadata
	 */
	public function get_metadata(){
		return $this->metadata;
	}

	/**
	 * Gets a metadata value for the post
	 *
	 * @param $string name	The metadata to retrieve
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
	 * Checks if a metadata item is set for the post
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
	 * @param $name		The metadata name to convert
	 * @return string	The converted name
	 */
	protected function normalise_meta_name($name){
		$clean	= preg_replace('%[^-/+|\w ]%', '', $name);
		$clean	= strtolower(trim($clean, '-'));
		$clean	= preg_replace('/[\/_|+ -]+/', '-', $clean);

		return $clean;
	}

	/**
	 * @return string	The URL to the post or to the linked article if set
	 */
	public function get_link(){
		if(!isset($this->link)){
			if($this->is_linked()){
				// Linked post
				$this->link	= $this->get_meta('link');
			} else {
				// Standard post
				$this->link	= $this->get_permalink();
			}
		}

		return $this->link;
	}

	/**
	 * @return string	The URL to the post
	 */
	public function get_permalink(){
		if(!isset($this->permalink)){
			$this->permalink	= $this->blog->get_url($this->get_relative_permalink());
		}

		return $this->permalink;
	}

	/**
	 * @return string	The URL to the post without the prefixed site URL
	 */
	public function get_relative_permalink(){
		return $this->date->format('Y/m').'/'.$this->slug;
	}

	public function get_year(){
		if(!isset($this->year)){
			$this->year	= new \Blight\Collections\Year($this->blog, $this->get_date()->format('Y'));
		}

		return $this->year;
	}

	public function get_tags(){
		if(!isset($this->tags) && $this->has_meta('tags')){
			$this->tags	= array_map(function($item){
				return new \Blight\Collections\Tag($this->blog, trim($item));
			}, explode(',', $this->get_meta('tags')));
		}

		return $this->tags;
	}

	public function get_category(){
		if(!isset($this->category) && $this->has_meta('category')){
			$this->category	= new \Blight\Collections\Category($this->blog, $this->get_meta('category'));
		}

		return $this->category;
	}

	/**
	 * @return bool	Whether the post is a linked post
	 */
	public function is_linked(){
		return $this->has_meta('link');
	}
}
