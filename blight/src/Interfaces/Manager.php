<?php
namespace Blight\Interfaces;

interface Manager {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @throws \RuntimeException	Posts directory cannot be opened
	 */
	public function __construct(\Blight\Interfaces\Blog $blog);

	/**
	 * @return array	An array of \Blight\Models\Page objects
	 */
	public function getPages();

	/**
	 * @return array	An array of \Blight\Models\Page objects
	 */
	public function getDraftPosts();

	/**
	 * @param array $filters	Any filters to apply
	 * 		array(
	 * 			'rss'	=> (bool|string)	// Whether to include RSS-only posts. Providing `'only'` will return only RSS-only posts
	 * 		)
	 * @return array			An array of posts
	 */
	public function getPosts($filters = null);

	/**
	 * @return array	An array of \Blight\Containers\Year objects containing posts
	 *
	 * 		Example:
	 * 		array(
	 * 			Year (
	 * 				getPosts()
	 * 			),
	 * 			Year (
	 * 				getPosts()
	 * 			)
	 * 		);
	 */
	public function getPostsByYear();

	/**
	 * @return array	An array of \Blight\Containers\Tag objects containing posts
	 *
	 * 		Example:
	 * 		array(
	 * 			Tag (
	 * 				getPosts()
	 * 			),
	 * 			Tag (
	 * 				getPosts()
	 * 			)
	 * 		);
	 */
	public function getPostsByTag();

	/**
	 * @return array	An array of \Blight\Containers\Category objects containing posts
	 *
	 * 		Example:
	 * 		array(
	 * 			Category (
	 * 				getPosts()
	 * 			),
	 * 			Category (
	 * 				getPosts()
	 * 			)
	 * 		);
	 */
	public function getPostsByCategory();

	/**
	 * @return array	An array of \Blight\Models\Post objects
	 */
	public function getSupplementaryPages();

	public function cleanupDrafts();
};
