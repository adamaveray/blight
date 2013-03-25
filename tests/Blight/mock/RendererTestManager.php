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

	public function getPages(){
		return $this->mock_pages;
	}

	public function getDraftPosts(){
		return $this->mock_posts['drafts'];
	}

	public function getPosts($filters = null){
		return $this->mock_posts['posts'];
	}

	public function getPostsByYear(){
		$posts	= $this->getPosts();
		$years	= array();

		foreach($posts as $post){
			/** @var \Blight\Post $post  */
			$y	= $post->getDate()->format('Y');
			if(!isset($years[$y])){
				$years[$y]	= new \Blight\Collections\Year($this->blog, $y);
			}

			$years[$y]->addPost($post);
		}

		return $years;
	}

	public function getPostsByTag(){
		$posts	= $this->getPosts();
		$tag_names	= array_map('trim', explode(',', $posts[0]->getMeta('tags')));
		$tag	= new \Blight\Collections\Tag($this->blog, current($tag_names));
		$tag->setPosts($posts);

		return array($tag);
	}

	public function getPostsByCategory(){
		$posts	= $this->getPosts();
		$category	= new \Blight\Collections\Category($this->blog, $posts[0]->getMeta('category'));
		$category->setPosts($posts);

		return array($category);
	}

	public function cleanupDrafts(){}
};
