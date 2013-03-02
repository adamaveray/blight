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
			'text'	=> new TextProcessor($this->blog),
			'archives'		=> array_keys($this->manager->get_posts_by_year()),
			'categories'	=> $this->manager->get_posts_by_category()
		));

		$template	= $this->blog->get_path_templates($file.'.php');
		if(!file_exists($template)){
			throw new \RuntimeException('Template "'.$file.'" not found');
		}

		extract($params);
		ob_start();
		include($template);
		return ob_get_clean();
	}

	/**
	 * Saves the provided content to the specificed file
	 *
	 * @param string $path		The file to write to
	 * @param string $content	The content to write to the file
	 */
	protected function write($path, $content){
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

		$pagination	= ($options['per_page'] > 0);

		$archive_dir	= $this->blog->get_path_www('/archive/');

		$years	= $this->manager->get_posts_by_year();
		foreach($years as $year => $posts){
			$page_title	= 'Archive '.$year;

			if($pagination){
				// Paginated
				$no_pages	= ceil(count($posts)/$options['per_page']);
				$pages	= array();
				for($page = 0; $page < $no_pages; $page++){
					$pages[$page+1]	= '/archive/'.$year.($page == 0 ? '' : '/'.($page+1));
				}

				// Build each page
				for($page = 0; $page < $no_pages; $page++){
					$content	= $this->build_template_file('archive', array(
						'year'	=> $year,
						'posts'	=> array_slice($posts, ($page-1)*$options['per_page'], $options['per_page']),
						'page_title'	=> $page_title,
						'pagination'	=> array(
							'current'	=> $page+1,
							'pages'		=> $pages
						)
					));

					$output_file	= $archive_dir.$year.'/'.($page == 0 ? 'index' : ($page+1)).'.html';

					$this->write($output_file, $content);
				}

			} else {
				// Single page
				$content	= $this->build_template_file('list', array(
					'year'	=> $year,
					'posts'	=> $posts,
					'page_title'	=> $page_title
				));
				$this->write($archive_dir.$year.'.html', $content);
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

		$pagination	= ($options['per_page'] > 0);

		$tags	= $this->manager->get_posts_by_tag();
		foreach($tags as $tag){
			/** @var $tag \Blight\Collections\Tag */
			$posts	= $tag->get_posts();

			$page_title	= 'Tag '.$tag->get_name();
			if($pagination){
				// Paginated
				$no_pages	= ceil(count($posts)/$options['per_page']);
				$pages	= array();
				for($page = 0; $page < $no_pages; $page++){
					$pages[$page+1]	= $tag->get_url().($page == 0 ? '' : '/'.($page+1));
				}

				// Build each page
				for($page = 0; $page < $no_pages; $page++){
					$content	= $this->build_template_file('list', array(
						'tag'	=> $tag,
						'posts'	=> array_slice($posts, ($page-1)*$options['per_page'], $options['per_page']),
						'page_title'	=> $page_title,
						'pagination'	=> array(
							'current'	=> $page+1,
							'pages'		=> $pages
						)
					));

					$output_file	= $tag->get_url().'/'.($page == 0 ? 'index' : ($page+1)).'.html';

					$this->write($output_file, $content);
				}

			} else {
				// Single page
				$content	= $this->build_template_file('list', array(
					'tag'	=> $tag,
					'posts'	=> $posts,
					'page_title'	=> $page_title
				));

				$this->write($tag->get_url().'.html', $content);
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

		$pagination	= ($options['per_page'] > 0);

		$categories	= $this->manager->get_posts_by_category();

		foreach($categories as $category){
			/** @var $category \Blight\Collections\Category */
			$posts	= $category->get_posts();

			$page_title	= 'Category '.$category->get_name();
			if($pagination){
				// Paginated
				$no_pages	= ceil(count($posts)/$options['per_page']);
				$pages	= array();
				for($page = 0; $page < $no_pages; $page++){
					$pages[$page+1]	= $category->get_url().($page == 0 ? '' : '/'.($page+1));
				}

				// Build each page
				for($page = 0; $page < $no_pages; $page++){
					$content	= $this->build_template_file('list', array(
						'category'	=> $category,
						'posts'		=> array_slice($posts, ($page-1)*$options['per_page'], $options['per_page']),
						'page_title'	=> $page_title,
						'pagination'	=> array(
							'current'	=> $page+1,
							'pages'		=> $pages
						)
					));

					$output_file	= $category->get_url().'/'.($page == 0 ? 'index' : ($page+1)).'.html';

					$this->write($output_file, $content);
				}

			} else {
				// Single page
				$content	= $this->build_template_file('list', array(
					'category'	=> $category,
					'posts'		=> $posts,
					'page_title'	=> $page_title
				));

				$this->write($category->get_url().'.html', $content);
			}
		}
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