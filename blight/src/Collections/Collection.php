<?php
namespace Blight\Collections;

abstract class Collection implements \Blight\Interfaces\Collection {
	/** @var \Blight\Blog */
	protected $blog;
	protected $slug;
	protected $name;

	public function __construct(\Blight\Blog $blog, $name){
		$this->blog	= $blog;
		$this->name	= $name;
	}

	public function get_name(){
		return $this->name;
	}

	public function get_slug(){
		if(!isset($this->slug)){
			$this->slug	= $this->name;
		}

		return $this->slug;
	}

	public function get_url(){
		return $this->blog->get_url($this->get_slug());
	}
};