<?php
namespace Blight\Tests;

class PostTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	protected $content_title;
	protected $content_date;
	protected $content_slug;
	protected $content_text;
	protected $content_tags;
	protected $content_category;
	protected $content_metadata;
	protected $content;
	protected $linked_content;
	protected $linked_content_link;

	/** @var \Blight\Interfaces\Post */
	protected $post;

	public function setUp(){
		global $config;
		$this->blog	= new \Blight\Blog($config);

		$this->content_title	= 'Test Post';
		$this->content_slug		= 'test-post';
		$this->content_date		= new \DateTime();
		$this->content_tags		= array(
			'Test Tag',
			'Other Tag'
		);
		$this->content_category	= 'General';

		$this->content_metadata	= array(
			'Date'		=> $this->content_date->format('Y-m-d H:i:s'),
			'Test Meta'	=> 'Test Value',
			'Tags'		=> implode(', ', $this->content_tags),
			'Category'	=> $this->content_category
		);

		$meta	= array();
		foreach($this->content_metadata as $key => $value){
			$meta[]	= $key.': '.$value;
		}
		$meta	= implode("\n", $meta);

		$this->content_text		= 'Test content.';

		$this->content	= <<<EOD
$this->content_title
=========
$meta

$this->content_text
EOD;

		$this->post	= new \Blight\Post($this->blog, $this->content, $this->content_slug);

		$this->linked_content_link	= 'http://www.example.com/';
		$this->linked_content	= <<<EOD
$this->content_title
=========
Link: $this->linked_content_link
$meta

Test Content.
EOD;
	}

	/**
	 * @covers \Blight\Post::__construct
	 */
	public function testConstruct(){
		$post	= new \Blight\Post($this->blog, $this->content, $this->content_slug);
		$this->assertInstanceOf('\Blight\Post', $post);
	}

	/**
	 * @covers \Blight\Post::__construct
	 */
	public function testDraftConstruct(){
		$post	= new \Blight\Post($this->blog, $this->content, $this->content_slug, true);
		$this->assertInstanceOf('\Blight\Post', $post);
	}

	/**
	 * @covers \Blight\Post::__construct
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidPostConstruct(){
		$content	= <<<EOD
Test Post
EOD;
		new \Blight\Post($this->blog, $content, 'test');
	}

	/**
	 * @covers \Blight\Post::get_title
	 */
	public function testGetTitle(){
		$this->assertEquals($this->content_title, $this->post->get_title(true));


		// Test linkblogs

		global $config;
		$test_config	= $config;

		// Test non-linkblog
		$link_char	= '>';
		$test_config['linkblog']['linkblog']		= false;
		$test_config['linkblog']['link_character']	= $link_char;
		$blog	= new \Blight\Blog($test_config);
		// Non-linked post
		$post	= new \Blight\Post($blog, $this->content, $this->content_slug);
		$this->assertEquals($this->content_title, $post->get_title());
		// Linked post
		$post	= new \Blight\Post($blog, $this->linked_content, $this->content_slug);
		$this->assertEquals($link_char.' '.$this->content_title, $post->get_title());

		// Test linkblog
		$post_char	= '*';
		$test_config['linkblog']['linkblog']		= true;
		$test_config['linkblog']['post_character']	= $post_char;
		$blog	= new \Blight\Blog($test_config);
		// Non-linked post
		$post	= new \Blight\Post($blog, $this->content, $this->content_slug);
		$this->assertEquals($post_char.' '.$this->content_title, $post->get_title());
		// Linked post
		$post	= new \Blight\Post($blog, $this->linked_content, $this->content_slug);
		$this->assertEquals($this->content_title, $post->get_title());
	}

	/**
	 * @covers \Blight\Post::get_metadata
	 */
	public function testGetMetadata(){
		$meta	= array(
			'date'		=> $this->content_metadata['Date'],
			'test-meta'	=> $this->content_metadata['Test Meta'],
			'tags'		=> $this->content_metadata['Tags'],
			'category'	=> $this->content_metadata['Category']
		);

		$this->assertEquals($meta, $this->post->get_metadata());
	}

	/**
	 * @covers \Blight\Post::get_link
	 */
	public function testGetLink(){
		$url	= $this->blog->get_url($this->content_date->format('Y/m').'/'.$this->content_slug);
		$this->assertEquals($url, $this->post->get_link());

		// Test linked post
		$post	= new \Blight\Post($this->blog, $this->linked_content, $this->content_slug);
		$this->assertEquals($this->linked_content_link, $post->get_link());
	}

	/**
	 * @covers \Blight\Post::get_permalink
	 */
	public function testGetPermalink(){
		$url	= $this->blog->get_url($this->content_date->format('Y/m').'/'.$this->content_slug);
		$this->assertEquals($url, $this->post->get_permalink());
		$this->assertEquals($this->post->get_link(), $this->post->get_permalink());

		// Test linked post
		$post	= new \Blight\Post($this->blog, $this->linked_content, $this->content_slug);
		$url	= $this->blog->get_url($this->content_date->format('Y/m').'/'.$this->content_slug);
		$this->assertEquals($url, $post->get_permalink());
		$this->assertNotEquals($post->get_link(), $post->get_permalink());
	}

	/**
	 * @covers \Blight\Post::get_relative_permalink
	 */
	public function testGetRelativePermalink(){
		$url	= $this->content_date->format('Y/m').'/'.$this->content_slug;
		$this->assertEquals($url, $this->post->get_relative_permalink());

		// Test linked post
		$post	= new \Blight\Post($this->blog, $this->linked_content, $this->content_slug);
		$url	= $this->content_date->format('Y/m').'/'.$this->content_slug;
		$this->assertEquals($url, $post->get_relative_permalink());
	}

	/**
	 * @covers \Blight\Post::get_year
	 */
	public function testGetYear(){
		$year	= $this->post->get_year();
		$this->assertInstanceOf('\Blight\Collections\Year', $year);

		$this->assertEquals($this->content_date->format('Y'), $year->get_name());
	}

	/**
	 * @covers \Blight\Post::get_tags
	 */
	public function testGetTags(){
		$tags	= $this->post->get_tags();
		$this->assertTrue(is_array($tags));
		$this->assertEquals(count($this->content_tags), count($tags));

		foreach($tags as $tag){
			$this->assertInstanceOf('\Blight\Collections\Tag', $tag);
			$this->assertTrue(in_array($tag->get_name(), $this->content_tags));
		}
	}

	/**
	 * @covers \Blight\Post::get_tags
	 */
	public function testNoTagsGetTags(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-01-01

Test content
EOD;
		$post	= new \Blight\Post($this->blog, $content, 'test-post');
		$tags	= $post->get_tags();
		$this->assertTrue(is_array($tags));
		$this->assertEquals(0, count($tags));
	}

	/**
	 * @covers \Blight\Post::get_category
	 */
	public function testGetCategory(){
		$category	= $this->post->get_category();
		$this->assertInstanceOf('\Blight\Collections\Category', $category);

		$this->assertEquals($this->content_category, $category->get_name());
	}

	/**
	 * @covers \Blight\Post::get_category
	 */
	public function testNoCategoryGetCategory(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-01-01

Test content
EOD;
		$post	= new \Blight\Post($this->blog, $content, 'test-post');
		$category	= $post->get_category();
		$this->assertNull($category);
	}

	/**
	 * @covers \Blight\Post::is_draft
	 */
	public function testIsDraft(){
		$this->assertFalse($this->post->is_draft());

		$post	= new \Blight\Post($this->blog, $this->content, $this->content_slug, true);
		$this->assertTrue($post->is_draft());
	}

	/**
	 * @covers \Blight\Post::is_linked
	 */
	public function testIsLinked(){
		$this->assertFalse($this->post->is_linked());

		$post	= new \Blight\Post($this->blog, $this->linked_content, $this->content_slug);
		$this->assertTrue($post->is_linked());
	}
};