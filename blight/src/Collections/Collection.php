<?php
namespace Blight\Collections;

abstract class Collection implements \Blight\Interfaces\Collection, \Iterator {
	/** @var \Blight\Blog */
	protected $blog;
	protected $slug;
	protected $name;

	protected $posts;

	public function __construct(\Blight\Blog $blog, $name){
		$this->blog	= $blog;
		$this->name	= $name;
	}

	public function get_name(){
		return $this->name;
	}

	public function get_slug(){
		if(!isset($this->slug)){
			$this->slug	= $this->convert_to_slug($this->name);
		}

		return $this->slug;
	}

	public function get_url(){
		return $this->blog->get_url($this->get_slug());
	}


	public function set_posts($posts){
		$this->posts	= $posts;
	}

	public function add_post(\Blight\Post $post){
		if(!isset($this->posts)){
			$this->posts	= array();
		}

		$this->posts[]	= $post;
	}

	public function get_posts(){
		return $this->posts;
	}

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