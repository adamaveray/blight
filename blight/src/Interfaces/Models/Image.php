<?php
namespace Blight\Interfaces\Models;

interface Image {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @param string $url	The image URL
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $url);

	/**
	 * @param bool $absolute	Whether to return the absolute image URL or not
	 * @return string	The image URL
	 */
	public function getURL($absolute = false);

	/**
	 * @return string|null	The textual alternative for the image
	 */
	public function getText();

	/**
	 * @param string $text	The textual alternative for the image
	 */
	public function setText($text);

	/**
	 * @return bool	Whether the image has a title
	 */
	public function hasTitle();

	/**
	 * @return string|null	The image's title
	 */
	public function getTitle();

	/**
	 * @param string $title	The image's title
	 */
	public function setTitle($title);
};
