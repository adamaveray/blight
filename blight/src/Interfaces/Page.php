<?php
namespace Blight\Interfaces;

interface Page {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @param string $content	The raw Markdown content for the page
	 * @param string $slug		The page URL slug
	 * @throws \InvalidArgumentException	Page date is invalid
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $content, $slug);

	/**
	 * @return string	The page title
	 */
	public function get_title();

	/**
	 * @return string	The page slug
	 */
	public function get_slug();

	/**
	 * @return \DateTime	The page date
	 */
	public function get_date();

	/**
	 * @param \DateTime $date	The new date for the post
	 */
	public function set_date(\DateTime $date);

	/**
	 * @return string	The page's raw Markdown content
	 */
	public function get_content();

	/**
	 * @return array	The page metadata
	 */
	public function get_metadata();

	/**
	 * @param string $name	The metadata to retrieve
	 * @return mixed|null	The metadata value if set, or null
	 */
	public function get_meta($name);

	/**
	 * @param string $name	The meta to check if exists
	 * @return bool	If the meta exists
	 */
	public function has_meta($name);

	/**
	 * @return string	The URL to the page
	 */
	public function get_link();

	/**
	 * @return string	The URL to the page
	 */
	public function get_permalink();

	/**
	 * @return string	The URL to the page without the prefixed site URL
	 */
	public function get_relative_permalink();
};
