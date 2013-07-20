<?php
namespace Blight\Interfaces\Models;

interface Post extends \Blight\Interfaces\Models\Page {
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
	 * @return \Blight\Models\Collections\Year	The Year collection the post belongs in
	 */
	public function getYear();

	/**
	 * @return array	An array of Tag collections
	 */
	public function getTags();

	/**
	 * @return array	An array of Category collections
	 */
	public function getCategories();

	/**
	 * @return bool	Whether the post has a summary or not
	 */
	public function hasSummary();

	/**
	 * @param int|null $length	The maximum number of characters to allow in the summary
	 * @param string $append	A string to append if the summary is truncated
	 * @return string|null		The post's summary
	 */
	public function getSummary($length = null, $append = '…');

	/**
	 * @return \Blight\Interfaces\Models\Author|null	The post's author, or the site's default author if not set, or null if neither are set
	 */
	public function getAuthor();

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
