<?php
namespace Blight\Tests\Mock;

class RendererTestManager implements \Blight\Interfaces\Manager {
	protected $blog;
	protected $mock_posts	= array();
	protected $mock_pages	= array();

	public function __construct(\Blight\Interfaces\Blog $blog){
		$this->blog	= $blog;
	}

	public function set_mock_posts($posts, $type){
		$this->mock_posts[$type]	= $posts;
	}
	public function set_mock_pages($pages){
		$this->mock_pages	= $pages;
	}

	public function get_pages(){
		return $this->mock_pages;
	}

	public function get_draft_posts(){
		return $this->mock_posts['drafts'];
	}

	public function get_posts(){
		return $this->mock_posts['posts'];
	}

	public function get_posts_by_year(){
		$posts	= $this->get_posts();
		$years	= array();

		foreach($posts as $post){
			/** @var \Blight\Post $post  */
			$y	= $post->get_date()->format('Y');
			if(!isset($years[$y])){
				$years[$y]	= new \Blight\Collections\Year($this->blog, $y);
			}

			$years[$y]->add_post($post);
		}

		return $years;
	}

	public function get_posts_by_tag(){
		$posts	= $this->get_posts();
		$tag_names	= array_map('trim', explode(',', $posts[0]->get_meta('tags')));
		$tag	= new \Blight\Collections\Tag($this->blog, current($tag_names));
		$tag->set_posts($posts);

		return array($tag);
	}

	public function get_posts_by_category(){
		$posts	= $this->get_posts();
		$category	= new \Blight\Collections\Category($this->blog, $posts[0]->get_meta('category'));
		$category->set_posts($posts);

		return array($category);
	}

	public function cleanup_drafts(){}
};
