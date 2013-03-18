<?php
namespace Blight;

/**
 * Handles all generation and outputting of final static files created from posts
 */
class Renderer implements \Blight\Interfaces\Renderer {
	protected $blog;
	protected $manager;
	protected $theme;

	protected $output_dir;
	protected $template_dir;
	protected $posts;

	protected $inbuilt_templates	= array(
		'feed'		=> 'src/views/templates/',
		'sitemap'	=> 'src/views/templates/'
	);

	protected $twig_environment;

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

		if(!is_dir($blog->get_path_www())){
			try {
				$this->blog->get_file_system()->create_dir($blog->get_path_www());
			} catch(\Exception $e){
				throw new \RuntimeException('Output directory cannot be found');
			}
		}

		foreach($this->inbuilt_templates as $name => &$path){
			$path	= $this->blog->get_path_app($path);
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
	protected function render_template($name, $params = null){
		$params	= array_merge(array(
			'archives'		=> $this->manager->get_posts_by_year(),
			'categories'	=> $this->manager->get_posts_by_category()
		), (array)$params);

		$callback	= array($this->theme, 'render_'.$name);
		if(is_callable($callback)){
			return call_user_func($callback, $params);

		} elseif(isset($this->inbuilt_templates[$name])){
			// Use default template
			$template	= new \Blight\Template($this->blog, $this->theme, $name, $this->inbuilt_templates[$name]);
			return $template->render($params);

		} else {
			return $this->theme->render_template($name, $params);
		}
	}

	/**
	 * Saves the provided content to the specificed file
	 *
	 * @param string $path		The file to write to
	 * @param string $content	The content to write to the file
	 */
	protected function write($path, $content){
		$url	= $this->blog->get_url();
		if(strpos($path, $url) === 0){
			// Convert web path to file
			$path	= $this->blog->get_path_www(substr($path, strlen($url)));
		}

		$this->blog->get_file_system()->create_file($path, $content);
	}

	/**
	 * Builds a template file with the provided parameters, and writes the rendered content to the specified file
	 *
	 * @param string $template_name	The template to use
	 * @param string $output_path	The file to write to
	 * @param array|null $params	An array of variables to be assigned to the local scope of the template
	 *
	 * @see render_template
	 * @see write
	 */
	protected function render_template_to_file($template_name, $output_path, $params = null){
		$this->write($output_path, $this->render_template($template_name, $params));
	}

	/**
	 * Generates and saves the static file for the given page
	 *
	 * @param \Blight\Interfaces\Page $page	The page to generate an HTML page from
	 */
	public function render_page(\Blight\Interfaces\Page $page){
		$path	= $this->blog->get_path_www($page->get_relative_permalink().'.html');

		$this->render_template_to_file('page', $path, array(
			'page'			=> $page,
			'page_title'	=> $page->get_title()
		));
	}

	/**
	 * Generates and saves the static files for all pages. Pages are retrieved from the
	 * Manager set during construction
	 */
	public function render_pages(){
		$pages	= $this->manager->get_pages();

		foreach($pages as $page){
			/** @var \Blight\Interfaces\Page $page */
			$this->render_page($page);
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
	public function render_post(\Blight\Interfaces\Post $post, $prev = null, $next = null){
		if($post->is_being_published()){
			$this->blog->do_hook('will_publish_post', array(
				'post'	=> $post
			));
		}

		$path	= $this->blog->get_path_www($post->get_relative_permalink().'.html');

		$params	= array(
			'post'			=> $post,
			'page_title'	=> $post->get_title()
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

		$this->render_template_to_file('post', $path, $params);

		if($post->is_being_published()){
			$this->blog->do_hook('did_publish_post', array(
				'post'	=> $post
			));
		}
	}

	/**
	 * Generates and saves the static files for all draft posts.
	 */
	public function render_drafts(){
		$drafts	= $this->manager->get_draft_posts();

		$output_path	= $this->blog->get_path_drafts_web();

		foreach($drafts as $draft_post){
			/** @var \Blight\Interfaces\Post $draft_post */
			$path	= $output_path.$draft_post->get_slug().'.html';
			$this->render_template_to_file('post', $path, array(
				'post'			=> $draft_post,
				'page_title'	=> $draft_post->get_title()
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
	 * @see render_year
	 * @see render_collections
	 */
	public function render_archives($options = null){
		$this->render_collections($this->manager->get_posts_by_year(), 'year', 'Archive %s', $options);
	}

	/**
	 * Generates and saves the static files for posts in the provided year
	 *
	 * @param \Blight\Interfaces\Collection $year	The archive year to render
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see render_archives
	 * @see render_collection
	 */
	public function render_year(\Blight\Interfaces\Collection $year, $options = null){
		$this->render_collection($year, 'year', 'Archive '.$year->get_name(), $options);
	}

	/**
	 * Generates and saves the static files for posts grouped by tags. Posts are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see render_collections
	 */
	public function render_tags($options = null){
		$this->render_collections($this->manager->get_posts_by_tag(), 'tag', 'Tag %s', $options);
	}

	/**
	 * Generates and saves the static files for posts grouped by category. Posts are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 *
	 * @see render_collections
	 */
	public function render_categories($options = null){
		$this->render_collections($this->manager->get_posts_by_category(), 'category', 'Category %s', $options);
	}

	/**
	 * Generates and saves the static files for posts grouped by the provided collections
	 *
	 * @param array $collections	An array of \Blight\Interfaces\Collection objects
	 * @param string $collection_type	The name of collection, used to assign it as a template variable
	 * @param string $title_format	A sprintf-formatted string for each collection's page title. The collection
	 * 								name will be passed in (replacing %s)
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * @see sprintf
	 */
	protected function render_collections($collections, $collection_type, $title_format, $options = null){
		foreach($collections as $collection){
			/** @var \Blight\Interfaces\Collection $collection */
			$this->render_collection($collection, $collection_type, sprintf($title_format, $collection->get_name()), $options);
		}
	}

	/**
	 * Generates and saves the static file for posts within the provided collection
	 *
	 * @param \Blight\Interfaces\Collection $collection	The collection to render
	 * @param string $collection_type	The name of collection, used to assign it as a template variable
	 * @param string $page_title	The title to be used for the rendered collection page
	 * @param array|null $options	An array of options to alter the rendered pages
	 */
	protected function render_collection(\Blight\Interfaces\Collection $collection, $collection_type, $page_title, $options = null){
		$options	= array_merge(array(
			'per_page'	=> 0	// Default to no pagination
		), (array)$options);

		$pages	= $this->paginate_collection($collection, $options['per_page']);

		foreach($pages as $output_file => $page){
			$this->render_template_to_file('list', $output_file, array_merge(array(
				$collection_type	=> $collection,
				'page_title'		=> $page_title
			), $page));
		}
	}

	/**
	 * Retrieves Post objects from the given Collection, and splits them into
	 * pages based on the given per page amount.
	 *
	 * @param Interfaces\Collection $collection
	 * @param int $per_page	The maximum number of posts to show per page
	 * @return array	An associative array of pages to be created
	 *
	 *		array (
	 * 			'path-to-page'	=> array(
	 * 				'posts'	=> array(),
	 * 				\Pagination	=> (
	 * 					get_items()		// All pages
	 * 					get_position()	// Current page
	 * 				)
	 * 			)
	 *		)
	 */
	protected function paginate_collection(\Blight\Interfaces\Collection $collection, $per_page){
		$return_pages	= array();

		$posts	= $collection->get_posts();

		if($per_page == 0 || count($posts) <= $per_page){
			// No pagination necessary
			$return_pages[$collection->get_url().'.html']	= array(
				'posts'	=> $posts
			);

			return $return_pages;
		}

		$no_pages	= ceil(count($posts)/$per_page);
		$pages		= array();
		for($page = 0; $page < $no_pages; $page++){
			$pages[$page+1]	= $collection->get_url().($page == 0 ? '' : '/'.($page+1));
		}

		// Build each page
		for($page = 0; $page < $no_pages; $page++){
			$url	= $collection->get_url().'/'.($page == 0 ? 'index' : ($page+1)).'.html';
			$return_pages[$url]	= array(
				'posts'			=> array_slice($posts, ($page-1)*$per_page, $per_page),
				'pagination'	=> new \Blight\Pagination($pages, $page+1)
			);
		}

		return $return_pages;
	}

	/**
	 * Generates and saves the static file for the blog home page. Posts are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'limit':	An int specifying the number of posts to include. 0 includes all posts [Default: 20]
	 */
	public function render_home($options = null){
		$options	= array_merge(array(
			'limit'	=> 20
		), (array)$options);

		// Prepare posts
		$posts	= $this->manager->get_posts();

		if($options['limit'] > 0){
			$posts	= array_slice($posts, 0, $options['limit']);
		}

		$path	= $this->blog->get_path_www('index.html');

		$this->render_template_to_file('home', $path, array(
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
	public function render_feeds($options = null){
		$options	= array_merge(array(
			'limit'	=> 20,
			'subfeeds'	=> true
		), (array)$options);

		// Main site feed
		$this->render_feed('feed', $this->manager->get_posts(array(
			'rss'	=> true
		)), $options);


		if($options['subfeeds']){
			// Category feeds
			$categories	= $this->manager->get_posts_by_category();
			foreach($categories as $category){
				/** @var \Blight\Interfaces\Collection $category */
				$this->render_feed('category/'.$category->get_slug(), $category->get_posts(), $options);
			}

			// Tag feeds
			$tags	= $this->manager->get_posts_by_tag();
			foreach($tags as $tag){
				/** @var \Blight\Interfaces\Collection $tag */
				$this->render_feed('tag/'.$tag->get_slug(), $tag->get_posts(), $options);
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
	protected function render_feed($path, $posts, $options = null){
		$options	= array_merge(array(
			'limit'	=> 20
		), (array)$options);

		if($options['limit'] > 0){
			$posts	= array_slice($posts, 0, $options['limit']);
		}

		$path	= $this->blog->get_path_www($path.'.xml');

		$this->render_template_to_file('feed', $path, array(
			'posts'	=> $posts
		));
	}

	/**
	 * Generates and saves the static XML file for the blog's sitemap. Pages are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered document
	 */
	public function render_sitemap($options = null){
		$options	= array_merge(array(
		), (array)$options);

		// Prepare posts
		$pages	= $this->manager->get_pages();

		$path	= $this->blog->get_path_www('sitemap.xml');

		$this->render_template_to_file('sitemap', $path, array(
			'pages'	=> $pages
		));
	}


	/**
	 * Copies all static assets from the theme to the web directory
	 */
	public function update_theme_assets(){
		$this->update_assets($this->theme->get_path_assets());
	}

	/**
	 * Copies all static assets from the user assets directory to the web directory
	 */
	public function update_user_assets(){
		$this->update_assets($this->blog->get_path_assets());
	}

	/**
	 * Ensures all assets within the source directory exist in the public assets directory, copying
	 * only nonexistent or updated files.
	 *
	 * @param string $source_dir	The directory to copy assets from
	 */
	protected function update_assets($source_dir){
		$target_dir	= $this->blog->get_path_www().'assets/';

		if(!is_dir($source_dir)){
			// No assets in theme?
			return;
		}

		$this->blog->get_file_system()->copy_dir($source_dir, $target_dir, 0755, true, true, true);
	}
};