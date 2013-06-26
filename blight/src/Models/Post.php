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
		return $this->hasMeta('summary');
	}

	/**
	 * @return string	The post's summary
	 */
	public function getSummary(){
		if(!$this->hasSummary()){
			return null;
		}

		return $this->getMeta('summary');
	}

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
}
