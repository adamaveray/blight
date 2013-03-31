<?php
namespace Blight\Interfaces\Models;

interface Collection {
	/**
	 * @param \Blight\Interfaces\Blog $blog	The Blog object to use throughout the instance
	 * @param string $name					The collection instance's name
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $name);

	/**
	 * @return string	The collection instance's name
	 */
	public function getName();

	/**
	 * @return string	The URL slug
	 */
	public function getSlug();

	/**
	 * @return string	The web URL to this collection
	 * @see getSlug()
	 */
	public function getURL();

	/**
	 * @param array $posts	An array of Post objects
	 * @throws \InvalidArgumentException	Invalid posts
	 */
	public function setPosts($posts);

	/**
	 * @param \Blight\Interfaces\Models\Post $post	The post to add
	 */
	public function addPost(\Blight\Interfaces\Models\Post $post);

	/**
	 * @return array	An array of Post objects
	 */
	public function getPosts();
};