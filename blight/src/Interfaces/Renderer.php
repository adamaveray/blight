<?php
namespace Blight\Interfaces;

interface Renderer {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @param \Blight\Interfaces\Manager $manager
	 * @throws \RuntimeException	Web or templates directory cannot be found
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, \Blight\Interfaces\Manager $manager, \Blight\Interfaces\Packages\Theme $theme);

	/**
	 * @param \Blight\Interfaces\Page $page	The page to generate an HTML page from
	 */
	public function render_page(Page $page);

	public function render_pages();

	/**
	 * @param \Blight\Interfaces\Post $post	The post to generate the page for
	 * @param \Blight\Interfaces\Post|null $prev	The adjacent previous/older post to the given post
	 * @param \Blight\Interfaces\Post|null $next	The adjacent next/newer post to the given post
	 * @throws \InvalidArgumentException	Previous or next posts are not instances of \Blight\Interfaces\Post
	 */
	public function render_post(Post $post);

	public function render_drafts();

	/**
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see render_year
	 * @see render_collections
	 */
	public function render_archives($options = null);

	/**
	 * @param \Blight\Interfaces\Collection $year	The archive year to render
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see render_archives
	 * @see render_collection
	 */
	public function render_year(\Blight\Interfaces\Collection $year, $options = null);

	/**
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see render_collections
	 */
	public function render_tags($options = null);

	/**
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see render_collections
	 */
	public function render_categories($options = null);

	/**
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'limit':	An int specifying the number of posts to include. 0 includes all posts [Default: 20]
	 */
	public function render_home($options = null);

	/**
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		in 'limit':		The number of posts to include. 0 includes all posts [Default: 20]
	 * 		bool 'subfeed':	Whether to generate feeds for categories and tags [Default: true]
	 */
	public function render_feeds($options = null);

	/**
	 * @param array|null $options	An array of options to alter the rendered document
	 */
	public function render_sitemap($options = null);

	public function update_user_assets();

	public function update_theme_assets();
};
