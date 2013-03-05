<?php
namespace Blight;

/**
 * Handles all raw posts and provides basic sorting and processing functionality
 */
class Manager {
	protected $blog;

	protected $posts;
	protected $posts_by_year;
	protected $posts_by_tag;
	protected $posts_by_category;
	protected $draft_posts;

	/**
	 * @var array The extensions of files to consider posts
	 */
	protected $allowed_extensions	= array('md', 'markdown', 'mdown');

	/**
	 * Initialises the posts manager
	 *
	 * @param Blog $blog
	 * @throws \InvalidArgumentException	Posts directory cannot be opened
	 */
	public function __construct(Blog $blog){
		$this->blog	= $blog;

		if(!is_dir($blog->get_path_posts())){
			throw new \InvalidArgumentException('No directory given');
		}
	}

	/**
	 * Locates all files within the posts directory
	 *
	 * @param bool $drafts	Whether to return only drafts or only published posts
	 * @return array	A list of filenames for each post file found
	 */
	protected function get_raw_posts($drafts = false){
		$dir	= ($drafts ? $this->blog->get_path_drafts() : $this->blog->get_path_posts());
		$files	= glob($dir.'*.*');

		if(!$drafts){
			$files	= array_merge(
				$files,		// Unsorted
				glob($dir.'*/*/*.*')	// Sorted (YYYY/DD/post.md)
			);
		}

		return $files;
	}

	/**
	 * Converts a post file to a Post object
	 *
	 * @param string $raw_post	The path to a post file
	 * @return \Blight\Post		The post built from the provided file
	 */
	protected function build_post($raw_post){
		$content	= $this->blog->get_file_system()->load_file($raw_post);

		$filename	= pathinfo($raw_post, \PATHINFO_FILENAME);
		if(preg_match('/\\/(\d{4})\\/(\d{2})\\/\1-\2-\d\d-([^\/]*?)\.(\w*?)$/', $raw_post, $matches)){
			$filename	= $matches[3];
		}

		return new Post($this->blog, $content, $filename);
	}

	/**
	 * Moves a post source file to a more-logical location. Moves files to YYYY/MM/YYYY-MM-DD-post.md
	 *
	 * @param \Blight\Post $post	The post to move
	 * @param string $current_path	The current path to the post's file
	 */
	protected function organise_post_file(Post $post, $current_path){
		// Check for special headers
		$has_date		= $post->has_meta('date');
		$has_publish	= $post->has_meta('publish-now');
		if(!$has_date || $has_publish){
			$lines	= explode("\n", $this->blog->get_file_system()->load_file($current_path));

			if($has_publish){
				// Remove publish header
				$count	= count($lines);
				for($i = 2; $i < $count; $i++){
					$line	= rtrim($lines[$i]);
					if($line === ''){
						// Reached end of header
						break;
					}

					if(preg_match('/^publish[- ]now$/i', strtolower($line))){
						// Found header
						array_splice($lines, $i, 1);
						break;
					}
				}
			}

			if(!$has_date && ($has_publish || !$post->is_draft())){
				// Add date header
				$now	= new \DateTime();
				$post->set_date($now);
				$date_line	= 'Date:'."\t".$now->format(date('Y-m-d H:i:s'));
				array_splice($lines, 2, 0, $date_line);
			}

			// Update file
			$this->blog->get_file_system()->create_file($current_path, implode("\n", $lines));
		}

		// Build filename
		$new_path	= $post->get_relative_permalink().'.'.current($this->allowed_extensions);
		$new_path	= $this->blog->get_path_posts(pathinfo($new_path, \PATHINFO_DIRNAME).'/'.$post->get_date()->format('Y-m-d').'-'.pathinfo($new_path, \PATHINFO_BASENAME));

		if($current_path == $new_path){
			// Already moved
			return;
		}

		$this->blog->get_file_system()->move_file($current_path, $new_path, !$post->is_draft());	// Don't clean up drafts
	}

	/**
	 * Retrieves all draft posts found as Post objects
	 *
	 * @return array	An array of posts
	 */
	public function get_draft_posts(){
		if(!isset($this->draft_posts)){
			$files	= $this->get_raw_posts(true);
			$posts	= array();

			foreach($files as $file){
				$extension	= pathinfo($file, \PATHINFO_EXTENSION);
				if(!in_array($extension, $this->allowed_extensions)){
					// Unknown filetype - ignore
					continue;
				}

				$content	= $this->blog->get_file_system()->load_file($file);

				// Create post object
				try {
					$post	= new Post($this->blog, $content, pathinfo($file, \PATHINFO_FILENAME), true);
				} catch(\Exception $e){
					continue;
				}

				if($post->has_meta('publish-now')){
					// Publish post
					$this->organise_post_file($post, $file);
					continue;
				}

				$posts[]	= $post;
			}

			$this->draft_posts	= $posts;
		}

		return $this->draft_posts;
	}

	/**
	 * Retrieves all posts found as Post objects
	 *
	 * @return array	An array of posts
	 */
	public function get_posts(){
		if(!isset($this->posts)){
			$files	= $this->get_raw_posts();

			$posts	= array();

			foreach($files as $file){
				$extension	= pathinfo($file, \PATHINFO_EXTENSION);
				if(!in_array($extension, $this->allowed_extensions)){
					// Unknown filetype - ignore
					continue;
				}

				// Create post object
				try {
					$post	= $this->build_post($file);
				} catch(\Exception $e){
					continue;
				}

				// Organise source file
				$this->organise_post_file($post, $file);

				$posts[]	= $post;
			}

			usort($posts, function(Post $a, Post $b){
				$a_date	= $a->get_date();
				$b_date	= $b->get_date();

				if($a_date == $b_date){
					return 0;
				}
				return ($a_date < $b_date) ? 1 : -1;
			});

			$this->posts	= $posts;
		}

		return $this->posts;
	}

	/**
	 * Groups posts by year, tag and category
	 */
	protected function group_posts(){
		$this->posts_by_year		= array();
		$this->posts_by_tag			= array();
		$this->posts_by_category	= array();

		$posts	= $this->get_posts();

		foreach($posts as $post){
			// Group post by year
			$year	= $post->get_year();
			$slug	= $year->get_slug();
			if(!isset($this->posts_by_year[$slug])){
				$this->posts_by_year[$slug]	= $year;
			}
			$this->posts_by_year[$slug]->add_post($post);

			// Group post by tag
			$tags	= $post->get_tags();
			foreach($tags as $tag){
				$slug	= $tag->get_slug();
				if(!isset($this->posts_by_tag[$slug])){
					$this->posts_by_tag[$slug]	= $tag;
				}
				$this->posts_by_tag[$slug]->add_post($post);
			}

			// Group post by category
			$category	= $post->get_category();
			$slug		= $category->get_slug();
			if(!isset($this->posts_by_category[$slug])){
				$this->posts_by_category[$slug]	= $category;
			}
			$this->posts_by_category[$slug]->add_post($post);
		}

		ksort($this->posts_by_tag);
		ksort($this->posts_by_category);
	}

	/**
	 * Groups all posts by publication year
	 *
	 * @return array	An array of tags containing posts
	 *
	 * 		Example:
	 * 		array(
	 * 			Year (
	 * 				get_posts()
	 * 			),
	 * 			Year (
	 * 				get_posts()
	 * 			)
	 * 		);
	 */
	public function get_posts_by_year(){
		if(!isset($this->posts_by_tag)){
			$this->group_posts();
		}

		return $this->posts_by_year;
	}

	/**
	 * Groups posts by tag
	 *
	 * @return array	An array of tags containing posts
	 *
	 * 		Example:
	 * 		array(
	 * 			Tag (
	 * 				get_posts()
	 * 			),
	 * 			Tag (
	 * 				get_posts()
	 * 			)
	 * 		);
	 */
	public function get_posts_by_tag(){
		if(!isset($this->posts_by_tag)){
			$this->group_posts();
		}

		return $this->posts_by_tag;
	}

	/**
	 * Groups posts by category
	 *
	 * @return array	An array of categories containing posts
	 *
	 * 		Example:
	 * 		array(
	 * 			Category (
	 * 				get_posts()
	 * 			),
	 * 			Category (
	 * 				get_posts()
	 * 			)
	 * 		);
	 */
	public function get_posts_by_category(){
		if(!isset($this->posts_by_tag)){
			$this->group_posts();
		}

		return $this->posts_by_category;
	}

	/**
	 * Deletes any rendered drafts without an associated draft post
	 */
	public function cleanup_drafts(){
		$posts_dir	= $this->blog->get_path_drafts();
		$files	= glob($this->blog->get_path_drafts_web('*.html'));
		foreach($files as $file){
			$slug	= pathinfo($file, \PATHINFO_BASENAME);

			$found	= false;
			foreach($this->allowed_extensions as $ext){
				if(file_exists($posts_dir.$slug.'.'.$ext)){
					$found	= true;
					break;
				}
			}

			if($found){
				// Post exists - ignore
				continue;
			}

			// Post not found - remove
			$this->blog->get_file_system()->delete_file($file);
		}
	}
};