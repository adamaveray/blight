<?php
namespace Blight\Interfaces;

interface Manager {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @throws \RuntimeException	Posts directory cannot be opened
	 */
	public function __construct(\Blight\Interfaces\Blog $blog);

	/**
	 * @return array	An array of \Blight\Page objects
	 */
	public function get_pages();

	/**
	 * @return array	An array of \Blight\Page objects
	 */
	public function get_draft_posts();

	/**
	 * @param array $filters	Any filters to apply
	 * 		array(
	 * 			'rss'	=> (bool|string)	// Whether to include RSS-only posts. Providing `'only'` will return only RSS-only posts
	 * 		)
	 * @return array			An array of posts
	 */
	public function get_posts($filters = null);

	/**
	 * @return array	An array of \Blight\Containers\Year objects containing posts
	 *
	 * 		Example:
	 * 		array(
	 * 			Year (
	 * 				get_posts()
	 * 			),
	 * 			Year (
	 * 				get_posts()
	 * 			)
	 * 		);
	 */
	public function get_posts_by_year();

	/**
	 * @return array	An array of \Blight\Containers\Tag objects containing posts
	 *
	 * 		Example:
	 * 		array(
	 * 			Tag (
	 * 				get_posts()
	 * 			),
	 * 			Tag (
	 * 				get_posts()
	 * 			)
	 * 		);
	 */
	public function get_posts_by_tag();

	/**
	 * @return array	An array of \Blight\Containers\Category objects containing posts
	 *
	 * 		Example:
	 * 		array(
	 * 			Category (
	 * 				get_posts()
	 * 			),
	 * 			Category (
	 * 				get_posts()
	 * 			)
	 * 		);
	 */
	public function get_posts_by_category();

	public function cleanup_drafts();
};
