<?php
namespace Blight\Interfaces;

interface Post extends \Blight\Interfaces\Page {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @param string $content	The raw Markdown content for the post
	 * @param string $slug		The post URL slug
	 * @param bool $isDraft	Whether the post is a draft
	 * @throws \InvalidArgumentException	Article date is invalid
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $content, $slug, $isDraft = false);

	/**
	 * @param bool $raw	Whether to prepend any additional linkblog glyphs to the title
	 * @return string	The post title
	 */
	public function getTitle($raw = false);

	/**
	 * @return \Blight\Collections\Year	The Year collection the post belongs in
	 */
	public function getYear();

	/**
	 * @return array	An array of Tag collections
	 */
	public function getTags();

	/**
	 * @return \Blight\Collections\Category|null	The Category collection the post belongs in, or null
	 */
	public function getCategory();

	/**
	 * @return bool	Whether the post is being published during this build
	 */
	public function isBeingPublished();

	/**
	 * @param bool $isBeingPublished	Whether the post is being published during this build
	 */
	public function setBeingPublished($isBeingPublished);

	/**
	 * @return bool	Whether the post is a draft
	 */
	public function isDraft();

	/**
	 * @return bool	Whether the post is a linked post
	 */
	public function isLinked();
};
