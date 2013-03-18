<?php
namespace Blight\Interfaces;

interface Collection {
	/**
	 * @param \Blight\Interfaces\Blog $blog	The Blog object to use throughout the instance
	 * @param string $name					The collection instance's name
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $name);

	/**
	 * @return string	The collection instance's name
	 */
	public function get_name();

	/**
	 * @return string	The URL slug
	 */
	public function get_slug();

	/**
	 * @return string	The web URL to this collection
	 * @see get_slug()
	 */
	public function get_url();

	/**
	 * @param array $posts	An array of Post objects
	 * @throws \InvalidArgumentException	Invalid posts
	 */
	public function set_posts($posts);

	/**
	 * @param \Blight\Interfaces\Post $post	The post to add
	 */
	public function add_post(\Blight\Interfaces\Post $post);

	/**
	 * @return array	An array of Post objects
	 */
	public function get_posts();
};