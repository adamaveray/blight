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
		'feed'		=> 'src/views/templates/',
		'sitemap'	=> 'src/views/templates/'
	);

	/**
	 * Initialises the output renderer
	 *
	 * @param \Blight\Interfaces\Blog $blog
	 * @param \Blight\Interfaces\Manager $manager
	 * @throws \RuntimeException	Web or templates directory cannot be found
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, \Blight\Interfaces\Manager $manager, \Blight\Interfaces\Packages\Theme $theme){
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
	 * @param string $name			The template to use
	 * @param array|null $params	An array of variables to be assigned to the local scope of the template
	 * @return string	The rendered content from the template
	 * @throws \RuntimeException	Template cannot be found
	 */
	protected function renderTemplate($name, $params = null){
		$params	= array_merge(array(
			'archives'		=> $this->manager->getPostsByYear(),
			'categories'	=> $this->manager->getPostsByCategory()
		), (array)$params);

		$callback	= array($this->theme, 'render_'.$name);
		if(is_callable($callback)){
			return call_user_func($callback, $params);

		} elseif(isset($this->inbuiltTemplates[$name])){
			// Use default template
			$template	= new \Blight\Template($this->blog, $this->theme, $name, $this->inbuiltTemplates[$name]);
			return $template->render($params);

		} else {
			return $this->theme->renderTemplate($name, $params);
		}
	}

	/**
	 * Saves the provided content to the specificed file
	 *
	 * @param string $path		The file to write to
	 * @param string $content	The content to write to the file
	 */
	protected function write($path, $content){
		$url	= $this->blog->getURL();
		if(strpos($path, $url) === 0){
			// Convert web path to file
			$path	= $this->blog->getPathWWW(substr($path, strlen($url)));
		}

		$this->blog->getFileSystem()->createFile($path, $content);
	}

	/**
	 * Builds a template file with the provided parameters, and writes the rendered content to the specified file
	 *
	 * @param string $templateName	The template to use
	 * @param string $outputPath	The file to write to
	 * @param array|null $params	An array of variables to be assigned to the local scope of the template
	 *
	 * @see renderTemplate
	 * @see write
	 */
	protected function renderTemplateToFile($templateName, $outputPath, $params = null){
		$this->write($outputPath, $this->renderTemplate($templateName, $params));
	}

	/**
	 * Generates and saves the static file for the given page
	 *
	 * @param \Blight\Interfaces\Page $page	The page to generate an HTML page from
	 */
	public function renderPage(\Blight\Interfaces\Page $page){
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
			/** @var \Blight\Interfaces\Page $page */
			$this->renderPage($page);
		}
	}

	/**
	 * Generates and saves the static file for the given post
	 *
	 * @param \Blight\Interfaces\Post $post	The post to generate the page for
	 * @param \Blight\Interfaces\Post|null $prev	The adjacent previous/older post to the given post
	 * @param \Blight\Interfaces\Post|null $next	The adjacent next/newer post to the given post
	 * @throws \InvalidArgumentException	Previous or next posts are not instances of \Blight\Interfaces\Post
	 */
	public function renderPost(\Blight\Interfaces\Post $post, $prev = null, $next = null){
		if($post->isBeingPublished()){
			$this->blog->doHook('will_publish_post', array(
				'post'	=> $post
			));
		}

		$path	= $this->blog->getPathWWW($post->getRelativePermalink().'.html');

		$params	= array(
			'post'			=> $post,
			'page_title'	=> $post->getTitle()
		);
		if(isset($prev)){
			if(!($prev instanceof \Blight\Interfaces\Post)){
				throw new \InvalidArgumentException('Previous post must be instance of \Blight\Interfaces\Post');
			}
			$params['post_prev']	= $prev;
		}
		if(isset($next)){
			if(!($next instanceof \Blight\Interfaces\Post)){
				throw new \InvalidArgumentException('Next post must be instance of \Blight\Interfaces\Post');
			}
			$params['post_next']	= $next;
		}

		$this->renderTemplateToFile('post', $path, $params);

		if($post->isBeingPublished()){
			$this->blog->doHook('did_publish_post', array(
				'post'	=> $post
			));
		}
	}

	/**
	 * Generates and saves the static files for all draft posts.
	 */
	public function renderDrafts(){
		$drafts	= $this->manager->getDraftPosts();

		$outputPath	= $this->blog->getPathDraftsWeb();

		foreach($drafts as $draftPost){
			/** @var \Blight\Interfaces\Post $draftPost */
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
	 * @param \Blight\Interfaces\Collection $year	The archive year to render
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see renderArchives
	 * @see renderCollection
	 */
	public function renderYear(\Blight\Interfaces\Collection $year, $options = null){
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
	 * @param array $collections	An array of \Blight\Interfaces\Collection objects
	 * @param string $collectionType	The name of collection, used to assign it as a template variable
	 * @param string $titleFormat	A sprintf-formatted string for each collection's page title. The collection
	 * 								name will be passed in (replacing %s)
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * @see sprintf
	 */
	protected function renderCollections($collections, $collectionType, $titleFormat, $options = null){
		foreach($collections as $collection){
			/** @var \Blight\Interfaces\Collection $collection */
			$this->renderCollection($collection, $collectionType, sprintf($titleFormat, $collection->getName()), $options);
		}
	}

	/**
	 * Generates and saves the static file for posts within the provided collection
	 *
	 * @param \Blight\Interfaces\Collection $collection	The collection to render
	 * @param string $collectionType	The name of collection, used to assign it as a template variable
	 * @param string $pageTitle			The title to be used for the rendered collection page
	 * @param array|null $options		An array of options to alter the rendered pages
	 */
	protected function renderCollection(\Blight\Interfaces\Collection $collection, $collectionType, $pageTitle, $options = null){
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
	protected function paginateCollection(\Blight\Interfaces\Collection $collection, $perPage){
		$returnPages	= array();

		$posts	= $collection->getPosts();

		if($perPage == 0 || count($posts) <= $perPage){
			// No pagination necessary
			$returnPages[$collection->getURL().'.html']	= array(
				'posts'	=> $posts
			);

			return $returnPages;
		}

		$noPages	= ceil(count($posts)/$perPage);
		$pages		= array();
		for($page = 0; $page < $noPages; $page++){
			$pages[$page+1]	= $collection->getURL().($page == 0 ? '' : '/'.($page+1));
		}

		// Build each page
		for($page = 0; $page < $noPages; $page++){
			$url	= $collection->getURL().'/'.($page == 0 ? 'index' : ($page+1)).'.html';
			$returnPages[$url]	= array(
				'posts'			=> array_slice($posts, ($page-1)*$perPage, $perPage),
				'pagination'	=> new \Blight\Pagination($pages, $page+1)
			);
		}

		return $returnPages;
	}

	/**
	 * Generates and saves the static file for the blog home page. Posts are retrieved from the
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

		$this->renderTemplateToFile('home', $path, array(
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
				/** @var \Blight\Interfaces\Collection $category */
				$this->renderFeed('category/'.$category->getSlug(), $category->getPosts(), $options);
			}

			// Tag feeds
			$tags	= $this->manager->getPostsByTag();
			foreach($tags as $tag){
				/** @var \Blight\Interfaces\Collection $tag */
				$this->renderFeed('tag/'.$tag->getSlug(), $tag->getPosts(), $options);
			}
		}
	}

	/**
	 * Generates and saves the static XML file for an RSS feed.
	 *
	 * @param string $path	The path to save the XML file to
	 * @param array $posts	An array of \Blight\Interfaces\Post objects
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'limit':	An int specifying the number of posts to include. 0 includes all posts [Default: 20]
	 */
	protected function renderFeed($path, $posts, $options = null){
		$options	= array_merge(array(
			'limit'	=> 20
		), (array)$options);

		if($options['limit'] > 0){
			$posts	= array_slice($posts, 0, $options['limit']);
		}

		$path	= $this->blog->getPathWWW($path.'.xml');

		$this->renderTemplateToFile('feed', $path, array(
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