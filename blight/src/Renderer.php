<?php
namespace Blight;

/**
 * Handles all generation and outputting of final static files created from posts
 */
class Renderer implements \Blight\Interfaces\Renderer {
	protected $blog;
	protected $manager;
	protected $theme;

	protected $inbuiltTemplates	= array(
		'feed_atom'	=> 'src/views/templates/',
		'feed_rss'	=> 'src/views/templates/',
		'sitemap'	=> 'src/views/templates/'
	);

	/**
	 * Initialises the output renderer
	 *
	 * @param \Blight\Interfaces\Blog $blog
	 * @param \Blight\Interfaces\Manager $manager
	 * @throws \RuntimeException	Web or templates directory cannot be found
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, \Blight\Interfaces\Manager $manager, \Blight\Interfaces\Models\Packages\Theme $theme){
		$this->blog		= $blog;
		$this->manager	= $manager;
		$this->theme	= $theme;

		if(!is_dir($blog->getPathWWW())){
			try {
				$this->blog->getFileSystem()->createDir($blog->getPathWWW());
			} catch(\Exception $e){
				throw new \RuntimeException('Output directory cannot be found');
			}
		}

		foreach($this->inbuiltTemplates as $name => &$path){
			$path	= $this->blog->getPathApp($path);
		}
	}

	/**
	 * Builds a template file with the provided variables, and returns the generated HTML
	 *
	 * @param string|array $names	The template or templates to use
	 * @param array|null $params	An array of variables to be assigned to the local scope of the template
	 * @return string	The rendered content from the template
	 * @throws \RuntimeException	Template cannot be found
	 */
	protected function renderTemplate($names, $params = null){
		$params	= array_merge(array(
			'archives'		=> $this->manager->getPostsByYear(),
			'categories'	=> $this->manager->getPostsByCategory()
		), (array)$params);

		if(!is_array($names)){
			$names	= array($names);
		}

		foreach($names as $name){
			$callback	= array($this->theme, 'render_'.$name);
			if(is_callable($callback)){
				return call_user_func($callback, $params);

			} elseif(isset($this->inbuiltTemplates[$name])){
				// Use default template
				$template	= new \Blight\Models\Template($this->blog, $this->theme, $name, $this->inbuiltTemplates[$name]);

				return $template->render($params);
			}

			// No match - continue
		}

		// No special cases
		return $this->theme->renderTemplate($names, $params);
	}

	/**
	 * Saves the provided content to the specificed file
	 *
	 * @param string $path		The file to write to
	 * @param string $content	The content to write to the file
	 */
	protected function write($path, $content){
		$this->blog->getFileSystem()->createFile($path, $content);
	}

	/**
	 * Builds a template file with the provided parameters, and writes the rendered content to the specified file
	 *
	 * @param string|array $names	The template or templates to use
	 * @param string $outputPath	The file to write to
	 * @param array|null $params	An array of variables to be assigned to the local scope of the template
	 *
	 * @throws \InvalidArgumentException	The output path is not specified
	 *
	 * @see renderTemplate
	 * @see write
	 */
	protected function renderTemplateToFile($names, $outputPath, $params = null){
		if(!isset($outputPath) || trim($outputPath) == ''){
			throw new \InvalidArgumentException('No output path provided');
		}

		$this->write($outputPath, $this->renderTemplate($names, $params));
	}

	/**
	 * Generates and saves the static file for the given page
	 *
	 * @param \Blight\Interfaces\Models\Page $page	The page to generate an HTML page from
	 */
	public function renderPage(\Blight\Interfaces\Models\Page $page){
		$path	= $this->blog->getPathWWW($page->getRelativePermalink().'.html');

		$this->renderTemplateToFile('page', $path, array(
			'page'			=> $page,
			'page_title'	=> $page->getTitle()
		));
	}

	/**
	 * Generates and saves the static files for all pages. Pages are retrieved from the
	 * Manager set during construction
	 */
	public function renderPages(){
		$pages	= $this->manager->getPages();

		foreach($pages as $page){
			/** @var \Blight\Interfaces\Models\Page $page */
			$this->renderPage($page);
		}
	}

	/**
	 * Generates and saves the static file for the given post
	 *
	 * @param \Blight\Interfaces\Models\Post $post	The post to generate the page for
	 * @param \Blight\Interfaces\Models\Post|null $prev	The adjacent previous/older post to the given post
	 * @param \Blight\Interfaces\Models\Post|null $next	The adjacent next/newer post to the given post
	 * @throws \InvalidArgumentException	Previous or next posts are not instances of \Blight\Interfaces\Models\Post
	 */
	public function renderPost(\Blight\Interfaces\Models\Post $post, $prev = null, $next = null){
		if($post->isBeingPublished()){
			$this->blog->doHook('willPublishPost', array(
				'post'	=> $post
			));
		}

		$path	= $this->blog->getPathWWW($post->getRelativePermalink().'.html');

		$params	= array(
			'post'			=> $post,
			'page_title'	=> $post->getTitle()
		);
		if(isset($prev)){
			if(!($prev instanceof \Blight\Interfaces\Models\Post)){
				throw new \InvalidArgumentException('Previous post must be instance of \Blight\Interfaces\Models\Post');
			}
			$params['post_prev']	= $prev;
		}
		if(isset($next)){
			if(!($next instanceof \Blight\Interfaces\Models\Post)){
				throw new \InvalidArgumentException('Next post must be instance of \Blight\Interfaces\Models\Post');
			}
			$params['post_next']	= $next;
		}

		$this->renderTemplateToFile('post', $path, $params);

		if($post->isBeingPublished()){
			$this->blog->doHook('didPublishPost', array(
				'post'	=> $post
			));
		}
	}

	/**
	 * Generates and saves the static files for all draft posts.
	 */
	public function renderDrafts(array $drafts = null){
		$drafts	= (isset($drafts) ? $drafts : $this->manager->getDraftPosts());

		$outputPath	= $this->blog->getPathDraftsWeb();

		foreach($drafts as $draftPost){
			/** @var \Blight\Interfaces\Models\Post $draftPost */
			$path	= $outputPath.$draftPost->getSlug().'.html';
			$this->renderTemplateToFile('post', $path, array(
				'post'			=> $draftPost,
				'page_title'	=> $draftPost->getTitle()
			));
		}
	}

	/**
	 * Generates and saves the static files for posts grouped by years. Posts are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see renderYear
	 * @see renderCollections
	 */
	public function renderArchives($options = null){
		$this->renderCollections($this->manager->getPostsByYear(), 'year', 'Archive %s', $options);
	}

	/**
	 * Generates and saves the static files for posts in the provided year
	 *
	 * @param \Blight\Interfaces\Models\Collection $year	The archive year to render
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see renderArchives
	 * @see renderCollection
	 */
	public function renderYear(\Blight\Interfaces\Models\Collection $year, $options = null){
		$this->renderCollection($year, 'year', 'Archive '.$year->getName(), $options);
	}

	/**
	 * Generates and saves the static files for posts grouped by tags. Posts are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see renderCollections
	 */
	public function renderTags($options = null){
		$this->renderCollections($this->manager->getPostsByTag(), 'tag', 'Tag %s', $options);
	}

	/**
	 * Generates and saves the static files for posts grouped by category. Posts are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see renderCollections
	 */
	public function renderCategories($options = null){
		$this->renderCollections($this->manager->getPostsByCategory(), 'category', 'Category %s', $options);
	}

	/**
	 * Generates and saves the static files for posts grouped by the provided collections
	 *
	 * @param array $collections	An array of \Blight\Interfaces\Models\Collection objects
	 * @param string $collectionType	The name of collection, used to assign it as a template variable
	 * @param string $titleFormat	A sprintf-formatted string for each collection's page title. The collection
	 * 								name will be passed in (replacing %s)
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * @see sprintf
	 */
	protected function renderCollections($collections, $collectionType, $titleFormat, $options = null){
		foreach($collections as $collection){
			/** @var \Blight\Interfaces\Models\Collection $collection */
			$this->renderCollection($collection, $collectionType, sprintf($titleFormat, $collection->getName()), $options);
		}
	}

	/**
	 * Generates and saves the static file for posts within the provided collection
	 *
	 * @param \Blight\Interfaces\Models\Collection $collection	The collection to render
	 * @param string $collectionType	The name of collection, used to assign it as a template variable
	 * @param string $pageTitle			The title to be used for the rendered collection page
	 * @param array|null $options		An array of options to alter the rendered pages
	 */
	protected function renderCollection(\Blight\Interfaces\Models\Collection $collection, $collectionType, $pageTitle, $options = null){
		$options	= array_merge(array(
			'per_page'	=> 0	// Default to no pagination
		), (array)$options);

		$pages	= $this->paginateCollection($collection, $options['per_page']);

		foreach($pages as $outputFile => $page){
			$this->renderTemplateToFile('list', $outputFile, array_merge(array(
				$collectionType	=> $collection,
				'page_title'	=> $pageTitle
			), $page));
		}
	}

	/**
	 * Retrieves Post objects from the given Collection, and splits them into
	 * pages based on the given per page amount.
	 *
	 * @param Interfaces\Collection $collection
	 * @param int $perPage	The maximum number of posts to show per page
	 * @param callable|null	A callback to apply to each paginated item
	 * @return array	An associative array of pages to be created
	 *
	 *		array (
	 * 			'path-to-page'	=> array(
	 * 				'posts'	=> array(),
	 * 				\Pagination	=> (
	 * 					get_items()		// All pages
	 * 					getPosition()	// Current page
	 * 				)
	 * 			)
	 *		)
	 */
	protected function paginateCollection(\Blight\Interfaces\Models\Collection $collection, $perPage, $callback = null){
		$returnPages	= array();

		$posts	= $collection->getPosts();
		$base	= $this->blog->getPathWWW();

		if($perPage == 0 || count($posts) <= $perPage){
			// No pagination necessary
			$returnPages[$base.$collection->getURL(true).'.html']	= array(
				'posts'	=> $posts
			);

			return $returnPages;
		}

		$noPages	= ceil(count($posts)/$perPage);
		$pages		= array();
		for($page = 0; $page < $noPages; $page++){
			$pages[$page+1]	= $collection->getURL().($page == 0 ? '' : '/'.($page+1));
		}

		if(isset($callback) && is_callable($callback)){
			$result	= $callback($pages, $this->blog, $collection);
			if(isset($result)){
				$pages	= $result;
			}
		}

		// Build each page
		for($page = 0; $page < $noPages; $page++){
			$path	= $base.$collection->getURL(true).'/'.($page == 0 ? 'index' : ($page+1)).'.html';
			$returnPages[$path]	= array(
				'posts'			=> array_slice($posts, $page*$perPage, $perPage-1),
				'pagination'	=> new \Blight\Pagination($pages, $page+1)
			);
		}

		return $returnPages;
	}

	/**
	 * Generates and saves the static file for the blog home page and sequential posts. Posts are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 20]
	 */
	public function renderSequential($options = null){
		$options	= array_merge(array(
			'per_page'	=> 20
		), (array)$options);

		// Prepare posts
		$posts	= $this->manager->getPosts();

		$collection	= new \Blight\Models\Collections\Sequential($this->blog, 'Page');
		$collection->setPosts($posts);

		$pages	= $this->paginateCollection($collection, $options['per_page'], function($pages, \Blight\Interfaces\Blog $blog, \Blight\Interfaces\Models\Collection $collection){
			// Change home page
			$pages[1]	= $blog->getURL();
			return $pages;
		});

		$pageNo	= 0;
		foreach($pages as $outputFile => $page){
			if($pageNo == 0){
				// Home page
				$template	= 'home';
				$outputFile	= $this->blog->getPathWWW('index.html');
			} else {
				// Subsequent
				$template	= 'list';
				$page['page_title']	= 'Page '.($pageNo+1);
			}

			$this->renderTemplateToFile($template, $outputFile, array_merge(array(
				'sequential'	=> $collection
			), $page));

			$pageNo++;
		}
	}

	/**
	 * Generates and saves the static file for blog the home page. Posts are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'limit':	An int specifying the number of posts to include. 0 includes all posts [Default: 20]
	 */
	public function renderHome($options = null){
		$options	= array_merge(array(
			'limit'	=> 20
		), (array)$options);

		// Prepare posts
		$posts	= $this->manager->getPosts();

		if($options['limit'] > 0){
			$posts	= array_slice($posts, 0, $options['limit']);
		}

		$path	= $this->blog->getPathWWW('index.html');

		$this->renderTemplateToFile(array('home', 'list'), $path, array(
			'posts'	=> $posts
		));
	}

	/**
	 * Generates and saves the static XML file for the blog RSS feeds. Posts are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		in 'limit':		The number of posts to include. 0 includes all posts [Default: 20]
	 * 		bool 'subfeed':	Whether to generate feeds for categories and tags [Default: true]
	 */
	public function renderFeeds($options = null){
		$options	= array_merge(array(
			'limit'	=> 20,
			'subfeeds'	=> true
		), (array)$options);

		// Main site feed
		$this->renderFeed('feed', $this->manager->getPosts(array(
			'rss'	=> true
		)), $options);


		if($options['subfeeds']){
			// Category feeds
			$categories	= $this->manager->getPostsByCategory();
			foreach($categories as $category){
				/** @var \Blight\Interfaces\Models\Collection $category */
				$this->renderFeed('category/'.$category->getSlug(), $category->getPosts(), $options);
			}

			// Tag feeds
			$tags	= $this->manager->getPostsByTag();
			foreach($tags as $tag){
				/** @var \Blight\Interfaces\Models\Collection $tag */
				$this->renderFeed('tag/'.$tag->getSlug(), $tag->getPosts(), $options);
			}
		}
	}

	/**
	 * Generates and saves the static XML file for an RSS feed.
	 *
	 * @param string $path	The path to save the XML file to
	 * @param array $posts	An array of \Blight\Interfaces\Models\Post objects
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'limit':	An int specifying the number of posts to include. 0 includes all posts [Default: 20]
	 */
	protected function renderFeed($path, $posts, $options = null){
		$options	= array_merge(array(
			'limit'	=> 20
		), (array)$options);

		$validFormats	= array('atom', 'rss');
		$defaultFormat	= current($validFormats);
		if(!isset($options['format']) || !in_array($options['format'], $validFormats)){
			$options['format']	= $this->blog->get('output.feed_format', $defaultFormat);
			if(!in_array($options['format'], $validFormats)){
				// Invalid format in config
				$options['format']	= $defaultFormat;
			}
		}

		if($options['limit'] > 0){
			$posts	= array_slice($posts, 0, $options['limit']);
		}

		$path	= $this->blog->getPathWWW($path.'.xml');

		$this->renderTemplateToFile('feed_'.$options['format'], $path, array(
			'posts'	=> $posts
		));
	}

	/**
	 * Generates and saves the static XML file for the blog's sitemap. Pages are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered document
	 */
	public function renderSitemap($options = null){
		$options	= array_merge(array(
		), (array)$options);

		// Prepare posts
		$pages	= $this->manager->getPages();

		$path	= $this->blog->getPathWWW('sitemap.xml');

		$this->renderTemplateToFile('sitemap', $path, array(
			'pages'	=> $pages
		));
	}

	/**
	 * Generates and saves the static files for additional utility pages, such as the 404 page.
	 *
	 * @param array|null $options	An array of options to alter the rendered documents
	 */
	public function renderSupplementaryPages($options = null){
		$options	= array_merge(array(
			'limit'	=> 5
		), (array)$options);

		$pages	= $this->manager->getSupplementaryPages();

		$outputPath	= $this->blog->getPathWWW();

		foreach($pages as $pageID => $page){
			/** @var \Blight\Interfaces\Models\Page $page */
			$templates	= array('page');
			$params		= array();
			switch($pageID){
				case '404':
					array_unshift($templates, '404');
					$params['recent_posts']	= array_slice($this->manager->getPosts(), 0, $options['limit']);
					break;
			}

			$path	= $outputPath.$page->getSlug().'.html';
			$this->renderTemplateToFile($templates, $path, array_merge($params, array(
				'page'			=> $page,
				'page_title'	=> $page->getTitle()
			)));
		}
	}


	/**
	 * Copies all static assets from the theme to the web directory
	 */
	public function updateThemeAssets(){
		$this->updateAssets($this->theme->getPathAssets());
	}

	/**
	 * Copies all static assets from the user assets directory to the web directory
	 */
	public function updateUserAssets(){
		$this->updateAssets($this->blog->getPathAssets());
	}

	/**
	 * Ensures all assets within the source directory exist in the public assets directory, copying
	 * only nonexistent or updated files.
	 *
	 * @param string $sourceDir	The directory to copy assets from
	 */
	protected function updateAssets($sourceDir){
		$targetDir	= $this->blog->getPathWWW().'assets/';

		if(!is_dir($sourceDir)){
			// No assets in theme?
			return;
		}

		$this->blog->getFileSystem()->copyDir($sourceDir, $targetDir, 0755, true, true, true);
	}
};