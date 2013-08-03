<?php
namespace Blight\Interfaces\Models;

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
	public function getTitle();

	/**
	 * @return string	The page slug
	 */
	public function getSlug();

	/**
	 * @return \DateTime	The page date
	 */
	public function getDate();

	/**
	 * @param \DateTime $date	The new date for the post
	 */
	public function setDate(\DateTime $date);

	/**
	 * @return string|null	The path for the page's source file
	 */
	public function getFile();

	/**
	 * @param string $file	The path for the page's source file
	 */
	public function setFile($file);

	/**
	 * @return \DateTime	The page modified date
	 */
	public function getDateUpdated();

	/**
	 * @param \DateTime $date	The new modified date for the page
	 */
	public function setDateUpdated(\DateTime $date);

	/**
	 * @return string	The page's raw Markdown content
	 */
	public function getContent();

	/**
	 * @return array	The page metadata
	 */
	public function getMetadata();

	/**
	 * @param string $name	The metadata to retrieve
	 * @return mixed|null	The metadata value if set, or null
	 */
	public function getMeta($name);

	/**
	 * @param string $name	The meta to check if exists
	 * @return bool	If the meta exists
	 */
	public function hasMeta($name);

	/**
	 * @return string	The URL to the page
	 */
	public function getLink();

	/**
	 * @return string	The URL to the page
	 */
	public function getPermalink();

	/**
	 * @return string	The URL to the page without the prefixed site URL
	 */
	public function getRelativePermalink();
};
