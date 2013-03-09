<?php
namespace Blight\Tests\Mock;

class RendererTestManager implements \Blight\Interfaces\Manager {
	protected $blog;
	protected $mock_posts	= array();

	public function __construct(\Blight\Interfaces\Blog $blog){
		$this->blog	= $blog;
	}

	public function set_mock_posts($posts, $type){
		$this->mock_posts[$type]	= $posts;
	}

	public function get_draft_posts(){
		return $this->mock_posts['drafts'];
	}

	public function get_posts(){
		return $this->mock_posts['posts'];
	}

	public function get_posts_by_year(){
		$posts	= $this->get_posts();
		$year	= new \Blight\Collections\Year($this->blog, $posts[0]->get_date()->format('Y'));
		$year->set_posts($posts);

		return array($year);
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
