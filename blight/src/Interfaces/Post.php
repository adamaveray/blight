<?php
namespace Blight\Interfaces;

interface Post extends \Blight\Interfaces\Page {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @param string $content	The raw Markdown content for the post
	 * @param string $slug		The post URL slug
	 * @param bool $is_draft	Whether the post is a draft
	 * @throws \InvalidArgumentException	Article date is invalid
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $content, $slug, $is_draft = false);

	/**
	 * @param bool $raw	Whether to prepend any additional linkblog glyphs to the title
	 * @return string	The post title
	 */
	public function get_title($raw = false);

	/**
	 * @return \Blight\Collections\Year	The Year collection the post belongs in
	 */
	public function get_year();

	/**
	 * @return array	An array of Tag collections
	 */
	public function get_tags();

	/**
	 * @return \Blight\Collections\Category|null	The Category collection the post belongs in, or null
	 */
	public function get_category();

	/**
	 * @return bool	Whether the post is being published during this build
	 */
	public function is_being_published();

	/**
	 * @param bool $is_being_published	Whether the post is being published during this build
	 */
	public function set_being_published($is_being_published);

	/**
	 * @return bool	Whether the post is a draft
	 */
	public function is_draft();

	/**
	 * @return bool	Whether the post is a linked post
	 */
	public function is_linked();
};
