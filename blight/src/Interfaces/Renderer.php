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
	public function renderPage(Page $page);

	public function renderPages();

	/**
	 * @param \Blight\Interfaces\Post $post	The post to generate the page for
	 * @param \Blight\Interfaces\Post|null $prev	The adjacent previous/older post to the given post
	 * @param \Blight\Interfaces\Post|null $next	The adjacent next/newer post to the given post
	 * @throws \InvalidArgumentException	Previous or next posts are not instances of \Blight\Interfaces\Post
	 */
	public function renderPost(Post $post);

	public function renderDrafts();

	/**
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see renderYear
	 */
	public function renderArchives($options = null);

	/**
	 * @param \Blight\Interfaces\Collection $year	The archive year to render
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see renderArchives
	 */
	public function renderYear(\Blight\Interfaces\Collection $year, $options = null);

	/**
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 */
	public function renderTags($options = null);

	/**
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 */
	public function renderCategories($options = null);

	/**
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'limit':	An int specifying the number of posts to include. 0 includes all posts [Default: 20]
	 */
	public function renderHome($options = null);

	/**
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		in 'limit':		The number of posts to include. 0 includes all posts [Default: 20]
	 * 		bool 'subfeed':	Whether to generate feeds for categories and tags [Default: true]
	 */
	public function renderFeeds($options = null);

	/**
	 * @param array|null $options	An array of options to alter the rendered document
	 */
	public function renderSitemap($options = null);

	public function updateUserAssets();

	public function updateThemeAssets();
};
