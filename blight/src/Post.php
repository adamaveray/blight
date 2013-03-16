<?php
namespace Blight;

/**
 * A blog post
 */
class Post extends \Blight\Page implements \Blight\Interfaces\Post {
	protected $year;
	protected $tags;
	protected $category;
	protected $is_draft;
	protected $link;
	protected $is_being_published;

	/**
	 * Initialises a post and processes the metadata contained in the header block
	 *
	 * @param \Blight\Interfaces\Blog $blog
	 * @param string $content	The raw Markdown content for the post
	 * @param string $slug		The post URL slug
	 * @param bool $is_draft	Whether the post is a draft
	 * @throws \InvalidArgumentException	Article date is invalid
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $content, $slug, $is_draft = false){
		$this->blog	= $blog;

		$this->is_draft	= $is_draft;

		$data	= $this->parse_content($content);

		$this->title	= $data['title'];
		$this->content	= $data['content'];
		$this->metadata	= $data['metadata'];

		if($this->has_meta('date')){
			try {
				$this->date	= new \DateTime($this->get_meta('date'));
			} catch(\Exception $e){
				throw new \InvalidArgumentException('Article date invalid');
			}
		}

		$this->slug	= strtolower($slug);
	}


	/**
	 * @param bool $raw	Whether to prepend any additional linkblog glyphs to the title
	 * @return string	The post title
	 */
	public function get_title($raw = false){
		$prepend	= '';
		
		if(!$raw){
			$is_linkblog	= $this->blog->is_linkblog();
			$is_linkpost	= $this->is_linked();

			if($is_linkblog && !$is_linkpost){
				// Unlinked post - prepend glyph
				$prepend	= $this->blog->get('post_character', 'linkblog', '★').' ';

			} elseif(!$is_linkblog && $is_linkpost){
				// Linked post - prepend arrow
				$prepend	= $this->blog->get('link_character', 'linkblog', '→').' ';
			}
		}

		return $prepend.$this->title;
	}

	/**
	 * @return \DateTime	The post date
	 * @throws \RuntimeException	The post does not have a date set
	 */
	public function get_date(){
		if(!isset($this->date)){
			if($this->is_draft()){
				// Draft - use current date
				return new \DateTime();
			} else {
				throw new \RuntimeException('Post does not have date set');
			}
		}

		return $this->date;
	}

	/**
	 * @return string	The URL to the post or to the linked article if set
	 */
	public function get_link(){
		if(!isset($this->link)){
			if($this->is_linked()){
				// Linked post
				$this->link	= $this->get_meta('link');
			} else {
				// Standard post
				$this->link	= $this->get_permalink();
			}
		}

		return $this->link;
	}

	/**
	 * @return string	The URL to the post without the prefixed site URL
	 */
	public function get_relative_permalink(){
		return $this->get_date()->format('Y/m').'/'.$this->slug;
	}

	/**
	 * Creates a collection for the year the post was authored
	 *
	 * @return \Blight\Collections\Year	The Year collection the post belongs in
	 */
	public function get_year(){
		if(!isset($this->year)){
			$this->year	= new \Blight\Collections\Year($this->blog, $this->get_date()->format('Y'));
		}

		return $this->year;
	}

	/**
	 * Creates collections for each tag assigned to the post.
	 *
	 * The post could have no tags assigned, which would result in an
	 * empty array being returned.
	 *
	 * @return array	An array of Tag collections
	 */
	public function get_tags(){
		if(!isset($this->tags)){
			if($this->has_meta('tags')){
				$tags	= array_map('trim', explode(',', $this->get_meta('tags')));
				$this->tags	= array_map(function($item){
					return new \Blight\Collections\Tag($this->blog, $item);
				}, array_unique($tags));
			} else {
				// No tags
				$this->tags	= array();
			}
		}

		return $this->tags;
	}

	/**
	 * Creates a collection for the category the post has assigned.
	 *
	 * If the post has no category assigned, null will be returned
	 *
	 * @return \Blight\Collections\Category|null	The Category collection the post belongs in, or null
	 */
	public function get_category(){
		if(!isset($this->category) && $this->has_meta('category')){
			$this->category	= new \Blight\Collections\Category($this->blog, $this->get_meta('category'));
		}

		return $this->category;
	}

	/**
	 * @return bool	Whether the post is being published during this build
	 */
	public function is_being_published(){
		return (bool)$this->is_being_published;
	}

	/**
	 * @param bool $is_being_published	Whether the post is being published during this build
	 */
	public function set_being_published($is_being_published){
		$this->is_being_published	= (bool)$is_being_published;
	}

	/**
	 * @return bool	Whether the post is a draft
	 */
	public function is_draft(){
		return (bool)$this->is_draft;
	}

	/**
	 * @return bool	Whether the post is a linked post
	 */
	public function is_linked(){
		return $this->has_meta('link');
	}
}
