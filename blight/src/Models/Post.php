<?php
namespace Blight\Models;

/**
 * A blog post
 */
class Post extends \Blight\Models\Page implements \Blight\Interfaces\Models\Post {
	protected $year;
	protected $tags;
	protected $categories;
	protected $isDraft;
	protected $link;
	protected $isBeingPublished;
	protected $summary;
	protected $images;

	/**
	 * Initialises a post and processes the metadata contained in the header block
	 *
	 * @param \Blight\Interfaces\Blog $blog
	 * @param string $content    The raw Markdown content for the post
	 * @param string $slug        The post URL slug
	 * @param bool $isDraft 	   Whether the post is a draft
	 * @throws \InvalidArgumentException    Article date is invalid
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $content, $slug, $isDraft = false){
		$this->blog = $blog;

		$this->isDraft = $isDraft;

		$data = $this->parseContent($content);

		$this->title = $data['title'];
		$this->content = $data['content'];
		$this->metadata = $data['metadata'];

		if($this->hasMeta('date')){
			try {
				$this->date = new \DateTime($this->getMeta('date'), $this->blog->getTimezone());
			} catch(\Exception $e){
				throw new \InvalidArgumentException('Article date invalid');
			}
		}

		$this->slug = strtolower($slug);
	}


	/**
	 * @param bool $raw	Whether to prepend any additional linkblog glyphs to the title
	 * @return string	The post title
	 */
	public function getTitle($raw = false){
		$prepend = '';

		if(!$raw){
			$isLinkblog	= $this->blog->isLinkblog();
			$isLinkpost	= $this->isLinked();

			if($isLinkblog && !$isLinkpost){
				// Unlinked post - prepend glyph
				$prepend = $this->blog->get('post_character', 'linkblog', '★') . ' ';

			} elseif(!$isLinkblog && $isLinkpost) {
				// Linked post - prepend arrow
				$prepend = $this->blog->get('link_character', 'linkblog', '→') . ' ';
			}
		}

		return $prepend . $this->title;
	}

	/**
	 * @return \DateTime    The post date
	 * @throws \RuntimeException    The post does not have a date set
	 */
	public function getDate(){
		if(!isset($this->date)){
			if($this->isDraft()){
				// Draft - use current date
				return new \DateTime('now', $this->blog->getTimezone());
			} else {
				throw new \RuntimeException('Post does not have date set');
			}
		}

		return $this->date;
	}

	/**
	 * @return string    The URL to the post or to the linked article if set
	 */
	public function getLink(){
		if(!isset($this->link)){
			if($this->isLinked()){
				// Linked post
				$this->link = $this->getMeta('link');
			} else {
				// Standard post
				$this->link = $this->getPermalink();
			}
		}

		return $this->link;
	}

	/**
	 * @return string    The URL to the post without the prefixed site URL
	 */
	public function getRelativePermalink(){
		$permalink = $this->getDate()->format('Y/m') . '/' . $this->slug;

		if($this->isLinked()){
			$prefix = $this->blog->get('link_directory', 'linkblog');
			if(isset($prefix)){
				$permalink = rtrim($prefix, '/') . '/' . $permalink;
			}
		}

		return $permalink;
	}

	/**
	 * Creates a collection for the year the post was authored
	 *
	 * @return \Blight\Models\Collections\Year    The Year collection the post belongs in
	 */
	public function getYear(){
		if(!isset($this->year)){
			$this->year = new \Blight\Models\Collections\Year($this->blog, $this->getDate()->format('Y'));
		}

		return $this->year;
	}

	/**
	 * Creates collections for each tag assigned to the post.
	 *
	 * If the post has no tags assigned, an empty array will be returned
	 *
	 * @return array    An array of Tag collections
	 */
	public function getTags(){
		if(!isset($this->tags)){
			if($this->hasMeta('tags')){
				$tags = array_map('trim', explode(',', $this->getMeta('tags')));
				$this->tags = array_map(function ($item){
					return new \Blight\Models\Collections\Tag($this->blog, $item);
				}, array_unique($tags));
			} else {
				// No tags
				$this->tags = array();
			}
		}

		return $this->tags;
	}

	/**
	 * Creates collections for each category assigned to the post.
	 *
	 * If the post has no categories assigned, an empty array will be returned
	 *
	 * @return array	An array of Category collections
	 */
	public function getCategories(){
		if(!isset($this->categories)){
			$rawCategories	= null;
			if($this->hasMeta('categories')){
				$rawCategories	= explode(',', $this->getMeta('categories'));
			} elseif($this->hasMeta('category')){
				$rawCategories	= array($this->getMeta('category'));
			}

			if(isset($rawCategories)){
				$this->categories	= array_map(function($item){
					return new \Blight\Models\Collections\Category($this->blog, $item);
				}, array_unique(array_map('trim', $rawCategories)));
			} else {
				$this->categories	= array();
			}
		}

		return $this->categories;
	}

	/**
	 * @return bool	Whether the post has a summary or not
	 */
	public function hasSummary(){
		return ($this->hasMeta('summary') || $this->blog->get('generate_summaries', 'output', true));
	}

	/**
	 * @param int|null $length	The maximum number of characters to allow in the summary
	 * @param string $append	A string to append if the summary is truncated
	 * @return string|null		The post's summary
	 */
	public function getSummary($length = null, $append = '…'){
		if(!$this->hasSummary()){
			// No summary
			return null;
		}

		if(!isset($this->summary)){
			if($this->hasMeta('summary')){
				$summary	= $this->getMeta('summary');

			} else {
				// Generate summary
				$replaces	= array(
					'~\!\[(.*?)\](\(.*?\)|\[.*?\])~'	=> '',		// Images
					'~\[(.*?)\](\(.*?\)|\[.*?\])~'		=> '$1',	// Links
					'~(\*\*?|__?)(.+?)(\*\*?|__|)~'		=> '$1',	// Bold/Emphasis
					'~\s*[\n\r]+\s*~'					=> ' ',		// Line breaks
				);

				$summary	= strip_tags(preg_replace(array_keys($replaces), array_values($replaces), $this->getContent()));
			}

			$this->summary	= $summary;
		}

		$summary	= $this->summary;
		if(isset($length)){
			// Limit
			$typo		= new \Blight\TextProcessor($this->blog);
			$summary	= $typo->truncateHTML($summary, $length, $append);
		}

		return $summary;
	}

	/**
	 * @return \Blight\Interfaces\Models\Author|null	The post's author, or the site's default author if not set, or null if neither are set
	 */
	public function getAuthor(){
		if(!isset($this->author)){
			$name	= null;
			if($this->hasMeta('author')){
				// Post-specific author
				$name	= $this->getMeta('author');
			}

			$this->author	= $this->blog->getAuthor($name);
		}

		return $this->author;
	}

	/**
	 * @return bool    Whether the post is being published during this build
	 */
	public function isBeingPublished(){
		return (bool)$this->isBeingPublished;
	}

	/**
	 * @param bool $isBeingPublished    Whether the post is being published during this build
	 */
	public function setBeingPublished($isBeingPublished){
		$this->isBeingPublished = (bool)$isBeingPublished;
	}

	/**
	 * @return bool    Whether the post is a draft
	 */
	public function isDraft(){
		return (bool)$this->isDraft;
	}

	/**
	 * @return bool    Whether the post is a linked post
	 */
	public function isLinked(){
		return $this->hasMeta('link');
	}

	/**
	 * @return array	An array of \Blight\Interfaces\Models\Image objects
	 */
	public function getImages(){
		if(!isset($this->images)){
			// Parse images from content
			preg_match_all('~\!\[(.*?)\]\((.*?)(?: "(.*?)")?\)~', $this->getContent(), $matches, \PREG_SET_ORDER);
			$images	= array();
			foreach($matches as $match){
				$image	= new \Blight\Models\Image($this->blog, $match[2]);
				$image->setText($match[1]);
				if(isset($match[3])){
					$image->setTitle($match[3]);
				}
				$images[]	= $image;
			}

			$this->images	= $images;
		}

		return $this->images;
	}
};
