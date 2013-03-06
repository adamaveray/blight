<?php
namespace Blight\Collections;

abstract class Collection implements \Blight\Interfaces\Collection, \Iterator {
	/** @var \Blight\Blog */
	protected $blog;

	/** @var string $slug	The collection's URL slug */
	protected $slug;
	/** @var string $name	The collection's name given in construction */
	protected $name;

	/** @var array $posts	An array of Post's added to the collection */
	protected $posts;


	/**
	 * @param \Blight\Blog $blog	The Blog object to use throughout the instance
	 * @param string $name			The collection instance's name
	 */
	public function __construct(\Blight\Blog $blog, $name){
		$this->blog	= $blog;
		$this->name	= $name;
	}

	/**
	 * @return string	The collection instance's name
	 */
	public function get_name(){
		return $this->name;
	}

	/**
	 * Retrieves the collection's URL slug, generating it from
	 * the collection's name if not set
	 *
	 * @return string	The URL slug
	 */
	public function get_slug(){
		if(!isset($this->slug)){
			$this->slug	= $this->convert_to_slug($this->name);
		}

		return $this->slug;
	}

	/**
	 * Retrieves the collection's full web URL, using the collection's slug
	 *
	 * @return string	The web URL to this collection
	 * @see get_slug()
	 */
	public function get_url(){
		return $this->blog->get_url($this->get_slug());
	}


	/**
	 * Sets the collection's Posts
	 *
	 * @param array $posts	An array of Post objects
	 * @throws \InvalidArgumentException	Invalid posts
	 */
	public function set_posts($posts){
		if(!is_array($posts)){
			throw new \InvalidArgumentException('Posts must be an array');
		}
		foreach($posts as $post){
			if(!($post instanceof \Blight\Post)){
				throw new \InvalidArgumentException('Posts must be instances of \Blight\Post');
			}
		}

		$this->posts	= $posts;
	}

	/**
	 * Adds a post to the collection's Posts.
	 * Duplicates are not checked for
	 *
	 * @param \Blight\Post $post	The post to add
	 */
	public function add_post(\Blight\Post $post){
		if(!isset($this->posts)){
			$this->posts	= array();
		}

		$this->posts[]	= $post;
	}

	/**
	 * Retrieves all posts added to the collection
	 *
	 * @return array	An array of Post objects
	 */
	public function get_posts(){
		if(!isset($this->posts)){
			$this->posts	= array();
		}
		return $this->posts;
	}


	/**
	 * Converts a name of any format to a standard URL-friendly slug
	 *
	 * @param string $name	The name to convert
	 * @return string		The converted URL slug
	 */
	protected function convert_to_slug($name){
		$clean	= preg_replace('%[^-/+|\w ]%', '', $name);
		$clean	= strtolower(trim($clean, '-'));
		$clean	= preg_replace('/[\/_|+ -]+/', '-', $clean);

		return $clean;
	}


	/* Iterator */
	protected $iterator_position	= 0;

	function rewind(){
		$this->iterator_position	= 0;
	}

	function current(){
		return $this->posts[$this->iterator_position];
	}

	function key(){
		return $this->iterator_position;
	}

	function next(){
		++$this->iterator_position;
	}

	function valid(){
		return isset($this->posts[$this->iterator_position]);
	}
};