<?php
namespace Blight\Interfaces;

use \Blight\Interfaces\Models\Page as PageInterface;
use \Blight\Interfaces\Models\Post as PostInterface;

interface Manager {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @throws \RuntimeException	Posts directory cannot be opened
	 */
	public function __construct(\Blight\Interfaces\Blog $blog);

	/**
	 * @return array	A list of filenames for each page file found
	 */
	public function getRawPages();

	/**
	 * @param bool $drafts	Whether to return only drafts or only published posts
	 * @return array	A list of filenames for each post file found
	 */
	public function getRawPosts($drafts = false);

	/**
	 * @param PostInterface $post
	 */
	public function addDraftToPublish(PostInterface $post);

	/**
	 * @return PostInterface[]
	 */
	public function getDraftsToPublish();

	/**
	 * @return PageInterface[]
	 */
	public function getPages();

	/**
	 * @return PostInterface[]
	 */
	public function getDraftPosts();

	/**
	 * @param array $filters	Any filters to apply
	 * 		array(
	 * 			'rss'	=> (bool|string)	// Whether to include RSS-only posts. Providing `'only'` will return only RSS-only posts
	 * 		)
	 * @return PostInterface[]
	 */
	public function getPosts($filters = null);

	/**
	 * @return \Blight\Containers\Year[]	Year container objects containing posts
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
	 * @return \Blight\Containers\Tag[]	Tag collection objects containing posts
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
	 * @return \Blight\Containers\Category[]	Category collection objects containing posts
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

	/**
	 * @param PostInterface[] $posts
	 * @return mixed
	 */
	public function publishDrafts(array $posts);

	public function cleanupDrafts();
};
