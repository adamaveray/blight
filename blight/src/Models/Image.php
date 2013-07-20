<?php
namespace Blight\Models;

class Image implements \Blight\Interfaces\Models\Image {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	protected $url;
	protected $text;
	protected $title;

	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @param string $url	The image URL
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $url){
		$this->blog	= $blog;

		$this->setURL($url);
	}

	/**
	 * @param bool $absolute	Whether to return the absolute image URL or not
	 * @return string	The image URL
	 */
	public function getURL($absolute = false){
		$url	= $this->url;
		if($absolute){
			$url	= $this->blog->getURL(ltrim($url, '/'));
		}

		return $url;
	}

	/**
	 * @param string $url	The image URL
	 */
	protected function setURL($url){
		$blogURL	= $this->blog->getURL();
		if(strpos($url, $blogURL) === 0){
			// Make relative
			$url	= '/'.ltrim(substr($url, strlen($blogURL)), '/');
		}

		$this->url	= $url;
	}

	/**
	 * @return string|null	The textual alternative for the image
	 */
	public function getText(){
		return $this->text;
	}

	/**
	 * @param string $text	The textual alternative for the image
	 */
	public function setText($text){
		$this->text	= $text;
	}

	/**
	 * @return bool	Whether the image has a title
	 */
	public function hasTitle(){
		return isset($this->title);
	}

	/**
	 * @return string|null	The image's title
	 */
	public function getTitle(){
		return $this->title;
	}

	/**
	 * @param string $title	The image's title
	 */
	public function setTitle($title){
		$this->title	= $title;
	}


	/**
	 * @param string $url
	 * @param string|null $text
	 * @param string|null $title
	 * @return \Blight\Models\Image
	 */
	public static function makeImage($url, $text = null, $title = null){
		$image	= new Image($url);
		$image->setText($text);
		$image->setTitle($title);

		return $image;
	}
};
