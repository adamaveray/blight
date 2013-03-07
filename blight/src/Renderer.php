<?php
namespace Blight;

/**
 * Handles all generation and outputting of final static files created from posts
 */
class Renderer implements \Blight\Interfaces\Renderer {
	protected $blog;
	protected $manager;

	protected $output_dir;
	protected $template_dir;
	protected $posts;

	protected $templates	= array();

	protected $twig_environment;

	/**
	 * Initialises the output renderer
	 *
	 * @param \Blight\Interfaces\Blog $blog
	 * @param \Blight\Interfaces\Manager $manager
	 * @throws \InvalidArgumentException	Web or template directories cannot be opened
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, \Blight\Interfaces\Manager $manager){
		$this->blog		= $blog;
		$this->manager	= $manager;

		if(!is_dir($blog->get_path_www())){
			try {
				$this->blog->get_file_system()->create_dir($blog->get_path_www());
			} catch(\Exception $e){
				throw new \InvalidArgumentException('Output directory cannot be found');
			}
		}
		if(!is_dir($blog->get_path_templates())){
			throw new \InvalidArgumentException('Templates directory cannot be found');
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
			'blog'			=> $this->blog,
			'archives'		=> $this->manager->get_posts_by_year(),
			'categories'	=> $this->manager->get_posts_by_category()
		), (array)$params);

		// Check if template cached
		if(!isset($this->templates[$name])){
			// Create template
			$this->templates[$name]	= new \Blight\Template($this->blog, $name);
		}

		return $this->templates[$name]->render($params);
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
	 * Generates and saves the static file for the given post
	 *
	 * @param \Blight\Interfaces\Post $post	The post to generate the page for
	 */
	public function render_post(\Blight\Interfaces\Post $post){
		$path	= $this->blog->get_path_www($post->get_relative_permalink().'.html');

		$this->render_template_to_file('post', $path, array(
			'post'			=> $post,
			'page_title'	=> $post->get_title()
		));
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
		), $options);

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
	 * 				'pagination'	=> array(
	 * 					'pages'		=> (array)[all pages]
	 * 					'current'	=> (int)[current page]
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
				'posts'	=> array_slice($posts, ($page-1)*$per_page, $per_page),
				'pagination'	=> array(
					'pages'		=> $pages,
					'current'	=> $page+1
				)
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
		), $options);

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
	 * Generates and saves the static XML file for the blog RSS feed. Posts are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'limit':	An int specifying the number of posts to include. 0 includes all posts [Default: 20]
	 */
	public function render_feed($options = null){
		$options	= array_merge(array(
			'limit'	=> 20
		), $options);

		// Prepare posts
		$posts	= $this->manager->get_posts();

		if($options['limit'] > 0){
			$posts	= array_slice($posts, 0, $options['limit']);
		}

		$path	= $this->blog->get_path_www('feed.xml');

		$this->render_template_to_file('feed', $path, array(
			'posts'	=> $posts
		));
	}
};