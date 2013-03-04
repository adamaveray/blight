<?php
namespace Blight;

/**
 * Handles all generation and outputting of final static files created from posts
 */
class Renderer {
	protected $blog;
	protected $manager;

	protected $output_dir;
	protected $template_dir;
	protected $posts;

	protected $twig_environment;

	/**
	 * Initialises the output renderer
	 *
	 * @param \Blight\Blog $blog
	 * @param \Blight\Manager $manager
	 * @throws \InvalidArgumentException	Web or template directories cannot be opened
	 */
	public function __construct(Blog $blog, Manager $manager){
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
	 * Extends an array with a second array, handling if the extending array is null
	 *
	 * @param array|null $options	The custom options that will overwrite any matching defaults, or null
	 * @param array $defaults		The default options to be used if not present in $options
	 * @return array				An array containing the merged options
	 */
	protected function extend_options($options, $defaults){
		if(!isset($options)){
			$options	= array();
		}

		return array_merge($defaults, $options);
	}

	/**
	 * Builds a post's rendered page, and returns the generated HTML
	 *
	 * @param Post $post	The post to build the page for
	 * @return string		The rendered content from the template
	 */
	protected function build_post_content(Post $post){
		$content	= $post->get_content();
		return $this->build_template_file('post', array(
			'post'	=> $post
		));
	}

	/**
	 * Builds a template file with the provided variables, and returns the generated HTML
	 *
	 * @param string $file			The template to use
	 * @param array|null $params	An array of variables to be assigned to the local scope of the template
	 * @return string	The rendered content from the template
	 * @throws \RuntimeException	Template cannot be found
	 */
	protected function build_template_file($file, $params = null){
		$params	= $this->extend_options($params, array(
			'blog'	=> $this->blog,
			'archives'		=> $this->manager->get_posts_by_year(),
			'categories'	=> $this->manager->get_posts_by_category()
		));

		$template	= $this->blog->get_path_templates($file);
		if(file_exists($template.'.php')){
			// Use PHP
			$params['text']	= new TextProcessor($this->blog);

			extract($params);
			ob_start();
			include($template.'.php');
			return ob_get_clean();

		} elseif(file_exists($template.'.tpl.html')){
			// Use Twig
			$twig	= $this->get_twig_environment();
			return $twig->render($file.'.tpl.html', $params);

		} else {
			// No template found
			throw new \RuntimeException('Template "'.$file.'" not found');
		}
	}

	/**
	 * Retrieves the standardised Twig environment object, with the correct template and cache paths set
	 *
	 * @return \Twig_Environment	The Twig environment object
	 */
	protected function get_twig_environment(){
		if(!isset($this->twig_environment)){
			$loader	= new \Twig_Loader_Filesystem($this->blog->get_path_templates());
			$this->twig_environment	= new \Twig_Environment($loader, array(
				'cache' => $this->blog->get_path_root('cache/')
			));

			// Set up filters
			$blog	= $this->blog;
			$text_processor	= new TextProcessor($this->blog);

			// Markdown filter
			$this->twig_environment->addFilter(new \Twig_SimpleFilter('md', function($string, $filter_typography = true) use($blog, $text_processor){
				$filters	= array(
					'markdown'		=> true,
					'typography'	=> true
				);
				if(!$filter_typography){
					$filters['typography']	= false;
				}
				return $text_processor->process($string, $filters);
			}, array(
				'is_safe' => array('html')
			)));

			// Typography filter
			$this->twig_environment->addFilter(new \Twig_SimpleFilter('typo', array($text_processor, 'process_typography'), array(
				'pre_escape'	=> 'html',
				'is_safe'		=> array('html')
			)));

			// Truncate filter
			$this->twig_environment->addFilter(new \Twig_SimpleFilter('truncate', array($text_processor, 'truncate_html')), array(
				'is_safe'	=> array('html')
			));
		}

		return $this->twig_environment;
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
	 * Generates and saves the static file for the given post
	 *
	 * @param Post $post	The post to generate the page for
	 */
	public function render_post(Post $post){
		$path	= $this->blog->get_path_www($post->get_relative_permalink().'.html');

		$content	= $this->build_post_content($post);

		$this->write($path, $content);
	}

	/**
	 * Generates and saves the static files for posts grouped by years. Posts are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 */
	public function render_archives($options = null){
		$options	= $this->extend_options($options, array(
			'per_page'	=> 0	// Default to no pagination
		));

		$years	= $this->manager->get_posts_by_year();

		foreach($years as $year){
			$pages	= $this->paginate_collection($year, $options['per_page']);

			$page_title	= 'Archive '.$year->get_name();
			foreach($pages as $output_file => $page){
				$content	= $this->build_template_file('list', $this->extend_options($page, array(
					'year'			=> $year,
					'page_title'	=> $page_title
				)));

				$this->write($output_file, $content);
			}
		}
	}

	/**
	 * Generates and saves the static files for posts grouped by tags. Posts are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 */
	public function render_tags($options = null){
		$options	= $this->extend_options($options, array(
			'per_page'	=> 0	// Default to no pagination
		));

		$tags	= $this->manager->get_posts_by_tag();

		foreach($tags as $tag){
			$pages	= $this->paginate_collection($tag, $options['per_page']);

			$page_title	= 'Tag '.$tag->get_name();
			foreach($pages as $output_file => $page){
				$content	= $this->build_template_file('list', $this->extend_options($page, array(
					'tag'			=> $tag,
					'page_title'	=> $page_title
				)));

				$this->write($output_file, $content);
			}
		}
	}

	/**
	 * Generates and saves the static files for posts grouped by category. Posts are retrieved from the
	 * Manager set during class construction.
	 *
	 * @param array|null $options	An array of options to alter the rendered pages
	 *
	 * 		'per_page':	An int specifying the number of posts to include per page. [Default: 0 (no pagination)]
	 */
	public function render_categories($options = null){
		$options	= $this->extend_options($options, array(
			'per_page'	=> 0	// Default to no pagination
		));

		$categories	= $this->manager->get_posts_by_category();

		foreach($categories as $category){
			$pages	= $this->paginate_collection($category, $options['per_page']);

			$page_title	= 'Category '.$category->get_name();
			foreach($pages as $output_file => $page){
				$content	= $this->build_template_file('list', $this->extend_options($page, array(
					'category'		=> $category,
					'page_title'	=> $page_title
				)));

				$this->write($output_file, $content);
			}
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
		$options	= $this->extend_options($options, array(
			'limit'	=> 20
		));

		// Prepare posts
		$posts	= $this->manager->get_posts();

		if($options['limit'] > 0){
			$posts	= array_slice($posts, 0, $options['limit']);
		}

		$path	= $this->blog->get_path_www('index.html');

		$content	= $this->build_template_file('home', array(
			'posts'	=> $posts
		));

		$this->write($path, $content);
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
		$options	= $this->extend_options($options, array(
			'limit'	=> 20
		));

		// Prepare posts
		$posts	= $this->manager->get_posts();

		if($options['limit'] > 0){
			$posts	= array_slice($posts, 0, $options['limit']);
		}

		$path	= $this->blog->get_path_www('feed.xml');

		$content	= $this->build_template_file('feed', array(
			'posts'	=> $posts
		));

		$this->write($path, $content);
	}
};