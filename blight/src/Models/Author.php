<?php
namespace Blight\Models;

class Author implements \Blight\Interfaces\Models\Author {
	/** @var \Blight\Interfaces\Blog $blog */
	protected $blog;

	protected $name;
	protected $email;
	protected $url;

	protected $attributes;


	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @param array $data
	 *
	 * 		$data	= array(
	 * 			'name'	=> '',	// Required
	 *			'email'	=> '',
	 *			'url'	=> ''
	 * 		)
	 *
	 * @throws \InvalidArgumentException	The data is missing the name
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $data){
		$this->blog	= $blog;

		if(!isset($data['name'])){
			throw new \InvalidArgumentException('The author `name` must be set');
		}

		$fields	= array(
			'name',
			'email',
			'url'
		);
		foreach($fields as $field){
			if(!isset($data[$field])){
				continue;
			}

			$this->$field	= $data[$field];
			unset($data[$field]);
		}

		$this->attributes	= array();
		foreach($data as $key => $value){
			$this->attributes[\Blight\Utilities::convertStringToSlug($key)]	= $value;
		}
	}

	/**
	 * @return string	The author's name
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * @return string	The author's email address
	 * @throws \RuntimeException	The author does not have an email address set
	 */
	public function getEmail(){
		if(!$this->hasEmail()){
			throw new \RuntimeException('Author does not have email');
		}

		return $this->email;
	}

	/**
	 * @return string	The author's URL
	 * @throws \RuntimeException	The author does not have a URL set
	 */
	public function getURL(){
		if(!$this->hasURL()){
			throw new \RuntimeException('Author does not have URL');
		}

		return $this->url;
	}

	/**
	 * @param string $name	The name of the attribute to retrieve
	 * @return mixed		The attribute value
	 * @throws \RuntimeException	The author does not have the requested attribute set
	 */
	public function getAttribute($name){
		if(!$this->hasAttribute($name)){
			throw new \RuntimeException('Author does not have attribute `'.$name.'`');
		}

		$name	= \Blight\Utilities::convertStringToSlug($name);
		return $this->attributes[$name];
	}

	/**
	 * @return bool	Whether the author has an email address set
	 */
	public function hasEmail(){
		return isset($this->email);
	}

	/**
	 * @return bool	Whether the author has a URL set
	 */
	public function hasURL(){
		return isset($this->url);
	}

	/**
	 * @param string $name	The name of the attribute to check for
	 * @return bool	Whether the attribute exists
	 */
	public function hasAttribute($name){
		$name	= \Blight\Utilities::convertStringToSlug($name);
		return isset($this->attributes[$name]);
	}


	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @param array $array	An array of associative-arrays for each author
	 * @return array		An array of \Blight\Models\Authors
	 */
	public static function arraysToAuthors(\Blight\Interfaces\Blog $blog, $array){
		$authors	= array();

		foreach($array as $rawAuthor){
			try {
				$author	= new \Blight\Models\Author($blog, $rawAuthor);
			} catch(\Exception $e){
				continue;
			}

			$authors[\Blight\Utilities::convertStringToSlug($author->getName())]	= $author;
		}

		return $authors;
	}
};

