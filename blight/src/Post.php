<?php
namespace Blight;

/**
 * A blog post
 */
class Post {
	protected $blog;

	protected $title;
	protected $slug;
	protected $date;
	protected $content;
	protected $settings;

	protected $link;
	protected $permalink;

	/**
	 * Initialises a post and processes the settings contained in the header block
	 *
	 * @param Blog $blog
	 * @param string $content	The raw Markdown content for the post
	 * @param string $slug		The post URL slug
	 * @throws \InvalidArgumentException	Article date is invalid
	 */
	public function __construct(Blog $blog, $content, $slug){
		$this->blog	= $blog;

		$data	= $this->parse_content($content);

		$this->title	= $data['title'];
		$this->content	= $data['content'];
		$this->settings	= $data['settings'];

		try {
			if(!isset($this->settings['date'])){
				throw new \Exception();
			}

			$this->date	= new \DateTime($this->settings['date']);

		} catch(\Exception $e){
			throw new \InvalidArgumentException('Article date invalid');
		}

		$this->slug	= strtolower($slug);
	}

	/**
	 * Processes the post settings contained in the header block
	 *
	 * @param string $content	The raw Markdown content for the post
	 * @return array			The settings retrieved from the post
	 * @throws \InvalidArgumentException	The article format is incorrect
	 * @see parse_settings()
	 */
	protected function parse_content($content){
		$lines	= explode($this->blog->get_eol(), $content);

		$title	= array_shift($lines);
		if(!preg_match('/^(\={3,})$/', rtrim(array_shift($lines)))){
			throw new \InvalidArgumentException('Article does not meet correct format');
		}

		$settings	= $this->parse_settings($lines);

		$content	= trim(implode($this->blog->get_eol(), $lines));

		return array(
			'title'		=> $title,
			'content'	=> $content,
			'settings'	=> $settings
		);
	}

	/**
	 * Processes the post settings contained in the header block, and strips those lines from
	 * the post content.
	 *
	 * @param array|string &$lines	The lines from the post body
	 * @return array	The settings contained within the lines
	 */
	protected function parse_settings(&$lines){
		if(!is_array($lines)){
			$lines	= explode($this->blog->get_eol(), $lines);
		}

		$settings	= array();
		while(true){
			$line	= trim(array_shift($lines));
			if($line == ''){
				// End of settings
				break;
			}

			$line	= array_map('trim', explode(':', $line, 2));
			if(count($line) != 2){
				// Unknown settings
				continue;
			}

			$settings[strtolower($line[0])]	= $line[1];
		}

		return $settings;
	}


	/**
	 * @param bool $raw	Whether to prepend any additional linkblog glyphs to the title
	 * @return string	The post title
	 */
	public function get_title($raw = false){
		if(!$raw){
			$is_linkblog	= $this->blog->is_linkblog();
			$is_linkpost	= $this->is_linked();

			$prepend	= '';
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
	 * @return string	The post slug
	 */
	public function get_slug(){
		return $this->slug;
	}

	/**
	 * @return \DateTime	The post date
	 */
	public function get_date(){
		return $this->date;
	}

	/**
	 * @return string	The post's raw Markdown content
	 */
	public function get_content(){
		return $this->content;
	}

	/**
	 * @return array	The post settings
	 */
	public function get_settings(){
		return $this->settings;
	}

	/**
	 * @return string	The URL to the post or to the linked article if set
	 */
	public function get_link(){
		if(!isset($this->link)){
			if($this->is_linked()){
				// Linked post
				$this->link	= $this->settings['link'];
			} else {
				// Standard post
				$this->link	= $this->get_permalink();
			}
		}

		return $this->link;
	}

	/**
	 * @return string	The URL to the post
	 */
	public function get_permalink(){
		if(!isset($this->permalink)){
			$this->permalink	= $this->blog->get_url($this->get_relative_permalink());
		}

		return $this->permalink;
	}

	/**
	 * @return string	The URL to the post without the prefixed site URL
	 */
	public function get_relative_permalink(){
		return $this->date->format('Y/m').'/'.$this->slug;
	}

	/**
	 * @return bool	Whether the post is a linked post
	 */
	public function is_linked(){
		return isset($this->settings['link']);
	}
}
