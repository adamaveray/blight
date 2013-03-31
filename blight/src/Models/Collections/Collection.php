<?php
namespace Blight\Models\Collections;

abstract class Collection implements \Blight\Interfaces\Models\Collection, \Iterator {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	/** @var string $slug	The collection's URL slug */
	protected $slug;
	/** @var string $name	The collection's name given in construction */
	protected $name;

	/** @var array $posts	An array of Post's added to the collection */
	protected $posts;


	/**
	 * @param \Blight\Interfaces\Blog $blog	The Blog object to use throughout the instance
	 * @param string $name					The collection instance's name
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $name){
		$this->blog	= $blog;
		$this->name	= $name;
	}

	/**
	 * @return string	The collection instance's name
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Retrieves the collection's URL slug, generating it from
	 * the collection's name if not set
	 *
	 * @return string	The URL slug
	 */
	public function getSlug(){
		if(!isset($this->slug)){
			$this->slug	= $this->convertToSlug($this->name);
		}

		return $this->slug;
	}

	/**
	 * Retrieves the collection's full web URL, using the collection's slug
	 *
	 * @return string	The web URL to this collection
	 * @see getSlug()
	 */
	public function getURL(){
		return $this->blog->getURL($this->getSlug());
	}


	/**
	 * Sets the collection's Posts
	 *
	 * @param array $posts	An array of Post objects
	 * @throws \InvalidArgumentException	Invalid posts
	 */
	public function setPosts($posts){
		if(!is_array($posts)){
			throw new \InvalidArgumentException('Posts must be an array');
		}
		foreach($posts as $post){
			if(!($post instanceof \Blight\Interfaces\Models\Post)){
				throw new \InvalidArgumentException('Posts must be instances of \Blight\Interfaces\Models\Post');
			}
		}

		$this->posts	= $posts;
	}

	/**
	 * Adds a post to the collection's Posts.
	 * Duplicates are not checked for
	 *
	 * @param \Blight\Interfaces\Models\Post $post	The post to add
	 */
	public function addPost(\Blight\Interfaces\Models\Post $post){
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
	public function getPosts(){
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
	protected function convertToSlug($name){
		$clean	= preg_replace('%[^-/+|\w ]%', '', $name);
		$clean	= strtolower(trim($clean, '-'));
		$clean	= preg_replace('/[\/_|+ -]+/', '-', $clean);

		return $clean;
	}


	/* Iterator */
	protected $iteratorPosition	= 0;

	function rewind(){
		$this->iteratorPosition	= 0;
	}

	function current(){
		return $this->posts[$this->iteratorPosition];
	}

	function key(){
		return $this->iteratorPosition;
	}

	function next(){
		++$this->iteratorPosition;
	}

	function valid(){
		return isset($this->posts[$this->iteratorPosition]);
	}
};