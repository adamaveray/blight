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

	/** @var \Blight\Interfaces\Models\Post */
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

		$this->post	= new \Blight\Models\Post($this->blog, $this->content, $this->content_slug);

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
	 * @covers \Blight\Models\Post::__construct
	 */
	public function testConstruct(){
		$post	= new \Blight\Models\Post($this->blog, $this->content, $this->content_slug);
		$this->assertInstanceOf('\Blight\Models\Post', $post);
	}

	/**
	 * @covers \Blight\Models\Post::__construct
	 */
	public function testDraftConstruct(){
		$post	= new \Blight\Models\Post($this->blog, $this->content, $this->content_slug, true);
		$this->assertInstanceOf('\Blight\Models\Post', $post);
	}

	/**
	 * @covers \Blight\Models\Post::__construct
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidPostConstruct(){
		$content	= <<<EOD
Test Post
EOD;
		new \Blight\Models\Post($this->blog, $content, 'test');
	}

	/**
	 * @covers \Blight\Models\Post::getTitle
	 */
	public function testGetTitle(){
		$this->assertEquals($this->content_title, $this->post->getTitle(true));


		// Test linkblogs

		global $config;
		$test_config	= $config;

		// Test non-linkblog
		$link_char	= '>';
		$test_config['linkblog']['linkblog']		= false;
		$test_config['linkblog']['link_character']	= $link_char;
		$blog	= new \Blight\Blog($test_config);
		// Non-linked post
		$post	= new \Blight\Models\Post($blog, $this->content, $this->content_slug);
		$this->assertEquals($this->content_title, $post->getTitle());
		// Linked post
		$post	= new \Blight\Models\Post($blog, $this->linked_content, $this->content_slug);
		$this->assertEquals($link_char.' '.$this->content_title, $post->getTitle());

		// Test linkblog
		$post_char	= '*';
		$test_config['linkblog']['linkblog']		= true;
		$test_config['linkblog']['post_character']	= $post_char;
		$blog	= new \Blight\Blog($test_config);
		// Non-linked post
		$post	= new \Blight\Models\Post($blog, $this->content, $this->content_slug);
		$this->assertEquals($post_char.' '.$this->content_title, $post->getTitle());
		// Linked post
		$post	= new \Blight\Models\Post($blog, $this->linked_content, $this->content_slug);
		$this->assertEquals($this->content_title, $post->getTitle());
	}

	/**
	 * @covers \Blight\Models\Post::getMetadata
	 */
	public function testGetMetadata(){
		$meta	= array(
			'date'		=> $this->content_metadata['Date'],
			'test-meta'	=> $this->content_metadata['Test Meta'],
			'tags'		=> $this->content_metadata['Tags'],
			'category'	=> $this->content_metadata['Category']
		);

		$this->assertEquals($meta, $this->post->getMetadata());
	}

	/**
	 * @covers \Blight\Models\Post::getLink
	 */
	public function testGetLink(){
		$url	= $this->blog->getURL($this->content_date->format('Y/m').'/'.$this->content_slug);
		$this->assertEquals($url, $this->post->getLink());

		// Test linked post
		$post	= new \Blight\Models\Post($this->blog, $this->linked_content, $this->content_slug);
		$this->assertEquals($this->linked_content_link, $post->getLink());
	}

	/**
	 * @covers \Blight\Models\Post::getPermalink
	 */
	public function testGetPermalink(){
		$url	= $this->blog->getURL($this->content_date->format('Y/m').'/'.$this->content_slug);
		$this->assertEquals($url, $this->post->getPermalink());
		$this->assertEquals($this->post->getLink(), $this->post->getPermalink());

		// Test linked post
		$post	= new \Blight\Models\Post($this->blog, $this->linked_content, $this->content_slug);
		$url	= $this->blog->getURL($this->content_date->format('Y/m').'/'.$this->content_slug);
		$this->assertEquals($url, $post->getPermalink());
		$this->assertNotEquals($post->getLink(), $post->getPermalink());
	}

	/**
	 * @covers \Blight\Models\Post::getRelativePermalink
	 */
	public function testGetRelativePermalink(){
		$url	= $this->content_date->format('Y/m').'/'.$this->content_slug;
		$this->assertEquals($url, $this->post->getRelativePermalink());

		// Test linked post
		$post	= new \Blight\Models\Post($this->blog, $this->linked_content, $this->content_slug);
		$url	= $this->content_date->format('Y/m').'/'.$this->content_slug;
		$this->assertEquals($url, $post->getRelativePermalink());
	}

	/**
	 * @covers \Blight\Models\Post::getYear
	 */
	public function testGetYear(){
		$year	= $this->post->getYear();
		$this->assertInstanceOf('\Blight\Models\Collections\Year', $year);

		$this->assertEquals($this->content_date->format('Y'), $year->getName());
	}

	/**
	 * @covers \Blight\Models\Post::getTags
	 */
	public function testGetTags(){
		$tags	= $this->post->getTags();
		$this->assertTrue(is_array($tags));
		$this->assertEquals(count($this->content_tags), count($tags));

		foreach($tags as $tag){
			$this->assertInstanceOf('\Blight\Models\Collections\Tag', $tag);
			$this->assertTrue(in_array($tag->getName(), $this->content_tags));
		}
	}

	/**
	 * @covers \Blight\Models\Post::getTags
	 */
	public function testDuplicateGetTags(){
		$raw_tags	= array(
			'Tag 1',
			'Tag 2',
			'Tag 3',
			'Tag 1'	// Duplicate;
		);
		$content	= <<<EOD
Test Post
=========
Date: 2013/02/01
Tags: {TAGS}

Test post
EOD;

		$post	= new \Blight\Models\Post($this->blog, str_replace('{TAGS}', implode(',', $raw_tags), $content), 'test-post');
		$tags	= $post->getTags();
		$this->assertTrue(is_array($tags));
		$this->assertNotEquals(count($raw_tags), count($tags));
		$this->assertEquals(count($raw_tags)-1, count($tags));

	}

	/**
	 * @covers \Blight\Models\Post::getTags
	 */
	public function testNoTagsGetTags(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-01-01

Test content
EOD;
		$post	= new \Blight\Models\Post($this->blog, $content, 'test-post');
		$tags	= $post->getTags();
		$this->assertTrue(is_array($tags));
		$this->assertEquals(0, count($tags));
	}

	/**
	 * @covers \Blight\Models\Post::getCategories
	 */
	public function testGetCategories(){
		$categories	= $this->post->getCategories();
		$this->assertTrue(count($categories) > 0);
		$this->assertInstanceOf('\Blight\Models\Collections\Category', current($categories));

		$this->assertEquals($this->content_category, current($categories)->getName());
	}

	/**
	 * @covers \Blight\Models\Post::hasSummary
	 */
	public function testHasSummary(){
		$this->assertFalse($this->post->hasSummary());

		$summary	= 'A test summary';
		$content	= $this->content;
		$content	= preg_replace('/(=+)(\n)/', '$1$2Summary: '.$summary.'$2', $content);

		$post	= new \Blight\Models\Post($this->blog, $content, 'test-post');
		$this->assertTrue($post->hasSummary());
	}

	/**
	 * @covers \Blight\Models\Post::getSummary
	 */
	public function testGetSummary(){
		$this->assertNull($this->post->getSummary());

		$summary	= 'A test summary';
		$content	= $this->content;
		$content	= preg_replace('/(=+)(\n)/', '$1$2Summary: '.$summary.'$2', $content);

		$post	= new \Blight\Models\Post($this->blog, $content, 'test-post');
		$this->assertEquals($summary, $post->getSummary());
	}

	/**
	 * @covers \Blight\Models\Post::getCategories
	 */
	public function testNoCategoryGetCategories(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-01-01

Test content
EOD;
		$post	= new \Blight\Models\Post($this->blog, $content, 'test-post');
		$categories	= $post->getCategories();

		$this->assertTrue(is_array($categories));
		$this->assertEmpty($categories);
	}

	/**
	 * @covers \Blight\Models\Post::isDraft
	 */
	public function testIsDraft(){
		$this->assertFalse($this->post->isDraft());

		$post	= new \Blight\Models\Post($this->blog, $this->content, $this->content_slug, true);
		$this->assertTrue($post->isDraft());
	}

	/**
	 * @covers \Blight\Models\Post::isBeingPublished
	 */
	public function testIsBeingPublished(){
		$this->assertFalse($this->post->isBeingPublished());
	}

	/**
	 * @covers \Blight\Models\Post::setBeingPublished
	 */
	public function testSetBeingPublished(){
		$this->assertFalse($this->post->isBeingPublished());
		$this->post->setBeingPublished(true);
		$this->assertTrue($this->post->isBeingPublished());
		$this->post->setBeingPublished(false);
		$this->assertFalse($this->post->isBeingPublished());
	}

	/**
	 * @covers \Blight\Models\Post::isLinked
	 */
	public function testIsLinked(){
		$this->assertFalse($this->post->isLinked());

		$post	= new \Blight\Models\Post($this->blog, $this->linked_content, $this->content_slug);
		$this->assertTrue($post->isLinked());
	}
};