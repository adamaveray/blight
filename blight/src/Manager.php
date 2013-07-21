<?php
namespace Blight;

/**
 * Handles all raw posts and provides basic sorting and processing functionality
 */
class Manager implements \Blight\Interfaces\Manager {
	const DRAFT_PUBLISH_DIR	= '_publish/';

	protected $blog;

	protected $pages;

	protected $posts;
	protected $postsByYear;
	protected $postsByTag;
	protected $postsByCategory;
	protected $draftPosts;
	protected $postExtension;

	/**
	 * @var array The extensions of files to consider posts
	 */
	protected $allowedExtensions	= array('md', 'markdown', 'mdown');

	/**
	 * Initialises the posts manager
	 *
	 * @param \Blight\Interfaces\Blog $blog
	 * @throws \RuntimeException	Posts directory cannot be opened
	 */
	public function __construct(\Blight\Interfaces\Blog $blog){
		$this->blog	= $blog;

		if(!is_dir($blog->getPathPosts())){
			throw new \RuntimeException('Posts directory not found');
		}

		if($blog->get('posts.allow_txt', false)){
			$this->allowedExtensions[]	= 'txt';
		}
		$this->postExtension	= ltrim($blog->get('posts.default_extension', current($this->allowedExtensions)), '.');
	}

	/**
	 * Locates all files within the pages directory
	 *
	 * @return array	A list of filenames for each page file found
	 */
	protected function getRawPages(){
		$dir	= $this->blog->getPathPages();

		function getSubPages($dir){
			$dir	= rtrim($dir, '/');

			$files	= array();

			$rawFiles	= glob($dir.'/*');
			foreach($rawFiles as $file){
				if(is_dir($file)){
					$files	= array_merge($files, getSubPages($file));
				} else {
					$files[]	= $file;
				}
			}

			return $files;
		};

		$files	= getSubPages($dir);

		return $files;
	}

	/**
	 * Locates all files within the posts directory
	 *
	 * @param bool $drafts	Whether to return only drafts or only published posts
	 * @return array	A list of filenames for each post file found
	 */
	protected function getRawPosts($drafts = false){
		$dir	= ($drafts ? $this->blog->getPathDrafts() : $this->blog->getPathPosts());
		$files	= glob($dir.'*.*');

		if(!$drafts){
			$draftPublishDir	= $this->blog->getPathDrafts(self::DRAFT_PUBLISH_DIR);

			$files	= array_merge(
				$files,		// Unsorted
				glob($dir.'*/*/*.*'),	// Sorted (YYYY/DD/post.md)
				glob($draftPublishDir.'*.*')	// Ready-to-publish drafts
			);
		}

		return $files;
	}

	/**
	 * Converts a post file to a Post object
	 *
	 * @param string $rawPost	The path to a post file
	 * @return \Blight\Interfaces\Models\Post		The post built from the provided file
	 */
	protected function buildPost($rawPost){
		$content	= $this->blog->getFileSystem()->loadFile($rawPost);

		$filename	= pathinfo($rawPost, \PATHINFO_FILENAME);
		if(preg_match('/\\/(\d{4})\\/(\d{2})\\/\1-\2-\d\d-([^\/]*?)\.(\w*?)$/', $rawPost, $matches)){
			$filename	= $matches[3];
		}

		$post	= new \Blight\Models\Post($this->blog, $content, $filename);

		// Modified time
		try {
			$date	= new \DateTime('@'.filemtime($rawPost));
			$date->setTimezone($this->blog->getTimezone());
			$post->setDateUpdated($date);
		} catch(\Exception $e){}

		return $post;
	}

	/**
	 * Moves a post source file to a more-logical location. Moves files to YYYY/MM/YYYY-MM-DD-post.md
	 *
	 * @param \Blight\Interfaces\Models\Post $post	The post to move
	 * @param string $currentPath	The current path to the post's file
	 * @return bool	Whether the post file was moved to be published
	 */
	protected function organisePostFile(\Blight\Interfaces\Models\Post $post, $currentPath){
		// Check for special headers
		$hasDate	= $post->hasMeta('date');
		$hasPublish		= $post->hasMeta('publish-now');
		$willPublish	= $post->getMeta('publish-at');
		if(isset($willPublish)){
			try {
				$willPublish	= new \DateTime($willPublish, $this->blog->getTimezone());
				if($willPublish > new \DateTime('now', $this->blog->getTimezone())){
					// Publish date in future - do not publish yet
					$willPublish	= null;
				}
			} catch(\Exception $e){
				$willPublish	= null;
			}
		}
		if(!$hasDate || $hasPublish || isset($willPublish)){
			$lines	= explode("\n", $this->blog->getFileSystem()->loadFile($currentPath));

			if($hasPublish || isset($willPublish)){
				// Remove publish header
				$searchLine	= ($hasPublish ? 'publish[- ]now' : 'publish[- ]at:.*?');

				$count	= count($lines);
				for($i = 2; $i < $count; $i++){
					$line	= rtrim($lines[$i]);
					if($line === ''){
						// Reached end of header
						break;
					}

					if(preg_match('/^'.$searchLine.'$/i', strtolower($line))){
						// Found header
						array_splice($lines, $i, 1);
						break;
					}
				}
			}

			if(!$hasDate && ($hasPublish || isset($willPublish) || !$post->isDraft())){
				// Add date header
				$date	= (isset($willPublish) ? $willPublish : new \DateTime('now', $this->blog->getTimezone()));
				$post->setDate($date);
				$dateLine	= 'Date:'."\t".$date->format('Y-m-d g:i:sa');
				array_splice($lines, 2, 0, $dateLine);
			}

			// Update file
			$this->blog->getFileSystem()->createFile($currentPath, implode("\n", $lines));
		}

		// Build filename
		$newPath	= $post->getDate()->format('Y/m/Y-m-d').'-'.$post->getSlug().'.'.$this->postExtension;
		$newPath	= $this->blog->getPathPosts($newPath);

		if($currentPath == $newPath){
			// Already moved and published
			return false;
		}

		// Move file
		$isDraftDir	= (strstr($currentPath, $this->blog->getPathDrafts()) !== false);
		$this->blog->getFileSystem()->moveFile($currentPath, $newPath, !$isDraftDir);	// Don't clean up drafts

		// Update modification time
		touch($newPath);

		// Moved - publishing
		return true;
	}

	/**
	 * Retrieves all pages found as Page objects
	 *
	 * @return array	An array of \Blight\Models\Page objects
	 */
	public function getPages(){
		if(!isset($this->pages)){
			$files	= $this->getRawPages();
			$dir	= $this->blog->getPathPages();

			$pages	= array();

			foreach($files as $file){
				$extension	= pathinfo($file, \PATHINFO_EXTENSION);
				if(!in_array($extension, $this->allowedExtensions)){
					// Unknown filetype - ignore
					continue;
				}

				$content	= $this->blog->getFileSystem()->loadFile($file);

				// Create page object
				try {
					$page	= new \Blight\Models\Page($this->blog, $content, preg_replace('/^(.*?)\.\w+?$/', '$1', str_replace($dir, '', $file)));
				} catch(\Exception $e){
					continue;
				}

				$date	= new \DateTime('@'.filemtime($file));
				$date->setTimezone($this->blog->getTimezone());
				$page->setDateUpdated($date);

				$pages[]	= $page;
			}

			$this->pages	= $pages;
		}

		return $this->pages;
	}

	/**
	 * Retrieves all draft posts found as Post objects
	 *
	 * @return array	An array of \Blight\Models\Page objects
	 */
	public function getDraftPosts(){
		if(!isset($this->draftPosts)){
			$files	= $this->getRawPosts(true);
			$posts	= array();

			foreach($files as $file){
				$extension	= pathinfo($file, \PATHINFO_EXTENSION);
				if(!in_array($extension, $this->allowedExtensions)){
					// Unknown filetype - ignore
					continue;
				}

				$content	= $this->blog->getFileSystem()->loadFile($file);

				// Create post object
				try {
					$post	= new \Blight\Models\Post($this->blog, $content, pathinfo($file, \PATHINFO_FILENAME), true);
				} catch(\Exception $e){
					continue;
				}

				$willPublish	= $post->hasMeta('publish-now');
				if(!$willPublish){
					try {
						$publishDate	= $post->getMeta('publish-at');
						if(isset($publishDate)){
							$publishDate	= new \DateTime($publishDate, $this->blog->getTimezone());
							$willPublish	= ($publishDate < new \DateTime('now', $this->blog->getTimezone()));
						}
					} catch(\Exception $e){
						$willPublish	= false;
					}
				}
				if($willPublish){
					// Move to publish directory
					$this->blog->getFileSystem()->moveFile($file, str_replace($this->blog->getPathDrafts(), $this->blog->getPathDrafts(self::DRAFT_PUBLISH_DIR), $file));
					continue;
				}

				$date	= new \DateTime('@'.filemtime($file));
				$date->setTimezone($this->blog->getTimezone());
				$post->setDateUpdated($date);

				$posts[]	= $post;
			}

			$this->draftPosts	= $posts;
		}

		return $this->draftPosts;
	}

	/**
	 * Retrieves all posts found as Post objects
	 *
	 * @param array $filters	Any filters to apply
	 * 		array(
	 * 			'rss'	=> (bool|string)	// Whether to include RSS-only posts. Providing `'only'` will return only RSS-only posts
	 * 		)
	 * @return array			An array of posts
	 */
	public function getPosts($filters = null){
		if(!isset($this->posts)){
			// Update drafts first
			$this->getDraftPosts();

			// Load files
			$files	= $this->getRawPosts();

			$posts	= array();

			foreach($files as $file){
				$extension	= pathinfo($file, \PATHINFO_EXTENSION);
				if(!in_array($extension, $this->allowedExtensions)){
					// Unknown filetype - ignore
					continue;
				}

				// Create post object
				try {
					$post	= $this->buildPost($file);
				} catch(\Exception $e){
					continue;
				}

				// Organise source file
				$willPublish	= $this->organisePostFile($post, $file);
				$post->setBeingPublished($willPublish);

				$posts[]	= $post;
			}

			usort($posts, function(\Blight\Interfaces\Models\Post $a, \Blight\Interfaces\Models\Post $b){
				$aDate	= $a->getDate();
				$bDate	= $b->getDate();

				if($aDate == $bDate){
					return 0;
				}
				return ($aDate < $bDate) ? 1 : -1;
			});

			$this->posts	= $posts;
		}

		$filters	= array_merge(array(
			'rss'	=> false
		), (array)$filters);

		$posts	= array();
		foreach($this->posts as $post){
			/** @var \Blight\Interfaces\Models\Post $post */
			if($filters['rss'] !== true){
				$isRSSOnly	= $post->getMeta('rss-only');
				if($filters['rss'] === 'only' && !$isRSSOnly){
					// Only allow RSS-only posts, post is not RSS-only
					continue;
				} elseif(!$filters['rss'] && $isRSSOnly){
					// Do not allow RSS-only posts, post is RSS-only
					continue;
				}
			}

			$posts[]	= $post;
		}

		return $posts;
	}

	/**
	 * Groups posts by year, tag and category
	 */
	protected function groupPosts(){
		$this->postsByYear		= array();
		$this->postsByTag			= array();
		$this->postsByCategory	= array();

		$posts	= $this->getPosts();

		foreach($posts as $post){
			// Group post by year
			$year	= $post->getYear();
			$slug	= $year->getSlug();
			if(!isset($this->postsByYear[$slug])){
				$this->postsByYear[$slug]	= $year;
			}
			$this->postsByYear[$slug]->addPost($post);

			// Group post by tag
			$tags	= $post->getTags();
			foreach($tags as $tag){
				$slug	= $tag->getSlug();
				if(!isset($this->postsByTag[$slug])){
					$this->postsByTag[$slug]	= $tag;
				}
				$this->postsByTag[$slug]->addPost($post);
			}

			// Group post by category
			$categories	= $post->getCategories();
			foreach($categories as $category){
				$slug	= $category->getSlug();
				if(!isset($this->postsByCategory[$slug])){
					$this->postsByCategory[$slug]	= $category;
				}
				$this->postsByCategory[$slug]->addPost($post);
			}
		}

		ksort($this->postsByTag);
		ksort($this->postsByCategory);
	}

	/**
	 * Groups all posts by publication year
	 *
	 * @return array	An array of \Blight\Containers\Year objects containing posts
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
	public function getPostsByYear(){
		if(!isset($this->postsByYear)){
			$this->groupPosts();
		}

		return $this->postsByYear;
	}

	/**
	 * Groups posts by tag
	 *
	 * @return array	An array of \Blight\Containers\Tag objects containing posts
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
	public function getPostsByTag(){
		if(!isset($this->postsByTag)){
			$this->groupPosts();
		}

		return $this->postsByTag;
	}

	/**
	 * Groups posts by category
	 *
	 * @return array	An array of \Blight\Containers\Category objects containing posts
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
	public function getPostsByCategory(){
		if(!isset($this->postsByTag)){
			$this->groupPosts();
		}

		return $this->postsByCategory;
	}

	/**
	 * Retrieves additional utility pages, such as the 404 page.
	 *
	 * @return array	An array of \Blight\Models\Post objects
	 */
	public function getSupplementaryPages(){
		$pages	= array();

		// 404 page
		$path	= $this->blog->getPathApp('src/views/pages/404.md');
		$pages['404']	= new \Blight\Models\Page($this->blog, $this->blog->getFileSystem()->loadFile($path), '404');

		return $pages;
	}

	/**
	 * Deletes any rendered drafts without an associated draft post
	 */
	public function cleanupDrafts(){
		$renderedExt	= 'html';

		$postsDir	= $this->blog->getPathDrafts();
		$files	= glob($this->blog->getPathDraftsWeb('*.'.$renderedExt));
		foreach($files as $file){
			$slug	= preg_replace('~\.'.$renderedExt.'$~i', '', pathinfo($file, \PATHINFO_BASENAME));

			$found	= false;
			foreach($this->allowedExtensions as $ext){
				if(file_exists($postsDir.$slug.'.'.$ext)){
					$found	= true;
					break;
				}
			}

			if($found){
				// Post exists - ignore
				continue;
			}

			// Post not found - remove
			$this->blog->getFileSystem()->deleteFile($file);
		}
	}
};