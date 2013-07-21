<?php
namespace Blight\Tests\Mock;

class RendererTestManager implements \Blight\Interfaces\Manager {
	protected $blog;
	protected $mockPosts	= array();
	protected $mockPages	= array();

	public function __construct(\Blight\Interfaces\Blog $blog){
		$this->blog	= $blog;
	}

	public function setMockPosts($posts, $type){
		$this->mockPosts[$type]	= $posts;
	}
	public function setMockPages($pages){
		$this->mockPages	= $pages;
	}

	public function getPages(){
		return $this->mockPages;
	}

	public function getDraftPosts(){
		return $this->mockPosts['drafts'];
	}

	public function getPosts($filters = null){
		return $this->mockPosts['posts'];
	}

	public function getPostsByYear(){
		$posts	= $this->getPosts();
		$years	= array();

		foreach($posts as $post){
			/** @var \Blight\Models\Post $post  */
			$y	= $post->getDate()->format('Y');
			if(!isset($years[$y])){
				$years[$y]	= new \Blight\Models\Collections\Year($this->blog, $y);
			}

			$years[$y]->addPost($post);
		}

		return $years;
	}

	public function getPostsByTag(){
		$posts	= $this->getPosts();
		$tagNames	= array_map('trim', explode(',', $posts[0]->getMeta('tags')));
		$tag	= new \Blight\Models\Collections\Tag($this->blog, current($tagNames));
		$tag->setPosts($posts);

		return array($tag);
	}

	public function getPostsByCategory(){
		$posts	= $this->getPosts();
		$category	= new \Blight\Models\Collections\Category($this->blog, $posts[0]->getMeta('category'));
		$category->setPosts($posts);

		return array($category);
	}

	public function getSupplementaryPages(){
		$pages	= array();

		// 404 page
		$path	= $this->blog->getPathApp('src/views/pages/404.md');
		$pages['404']	= new \Blight\Models\Page($this->blog, $this->blog->getFileSystem()->loadFile($path), '404');

		return $pages;
	}

	public function cleanupDrafts(){}
};
