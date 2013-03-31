<?php
namespace Blight\Tests;

class PageTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	protected $content_title;
	protected $content_date;
	protected $content_slug;
	protected $content_text;
	protected $content_metadata;
	protected $content;

	/** @var \Blight\Interfaces\Models\Page */
	protected $page;

	public function setUp(){
		global $config;
		$this->blog	= new \Blight\Blog($config);

		$this->content_title	= 'Test Page';
		$this->content_slug		= 'test-page';
		$this->content_date		= new \DateTime();
		$this->content_metadata	= array(
			'Date'		=> $this->content_date->format('Y-m-d H:i:s'),
			'Test Meta'	=> 'Test Value'
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

		$this->page	= new \Blight\Models\Page($this->blog, $this->content, $this->content_slug);
	}

	/**
	 * @covers \Blight\Models\Page::__construct
	 */
	public function testConstruct(){
		$post	= new \Blight\Models\Page($this->blog, $this->content, $this->content_slug);
		$this->assertInstanceOf('\Blight\Models\Page', $post);
	}

	/**
	 * @covers \Blight\Models\Page::__construct
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidPageConstruct(){
		$content	= <<<EOD
Not A Page
EOD;
		new \Blight\Models\Page($this->blog, $content, 'test');
	}

	/**
	 * @covers \Blight\Models\Page::getTitle
	 */
	public function testGetTitle(){
		$this->assertEquals($this->content_title, $this->page->getTitle(true));
	}

	/**
	 * @covers \Blight\Models\Page::getSlug
	 */
	public function testGetSlug(){
		$this->assertEquals($this->content_slug, $this->page->getSlug());
	}

	/**
	 * @covers \Blight\Models\Page::getDate
	 */
	public function testGetDate(){
		$this->assertEquals($this->content_date, $this->page->getDate());
	}

	/**
	 * @covers \Blight\Models\Page::setDate
	 */
	public function testSetDate(){
		$date	= new \DateTime('now');
		$this->page->setDate($date);
		$this->assertEquals($date, $this->page->getDate());
	}

	/**
	 * @covers \Blight\Models\Page::getDateUpdated
	 */
	public function testGetDateUpdated(){
		// Should default to date created
		$this->assertEquals($this->content_date, $this->page->getDateUpdated());
	}

	/**
	 * @covers \Blight\Models\Page::setDateUpdated
	 */
	public function testSetDateUpdated(){
		$date	= new \DateTime('now');
		$this->page->setDateUpdated($date);
		$this->assertEquals($date, $this->page->getDateUpdated());
	}

	/**
	 * @covers \Blight\Models\Page::getContent
	 */
	public function testGetContent(){
		$this->assertEquals($this->content_text, $this->page->getContent());
	}

	/**
	 * @covers \Blight\Models\Page::getMetadata
	 */
	public function testGetMetadata(){
		$meta	= array(
			'date'		=> $this->content_metadata['Date'],
			'test-meta'	=> $this->content_metadata['Test Meta']
		);

		$this->assertEquals($meta, $this->page->getMetadata());
	}

	/**
	 * @covers \Blight\Models\Page::getMeta
	 */
	public function testGetMeta(){
		$this->assertEquals($this->content_metadata['Test Meta'], $this->page->getMeta('Test Meta'));
		$this->assertEquals($this->content_metadata['Test Meta'], $this->page->getMeta('test-meta'));

		// Non-existent
		$this->assertNull($this->page->getMeta('nonexistent'));
	}

	/**
	 * @covers \Blight\Models\Page::hasMeta
	 */
	public function testHasMeta(){
		$this->assertTrue($this->page->hasMeta('Test Meta'));
		$this->assertTrue($this->page->hasMeta('test-meta'));
		$this->assertFalse($this->page->hasMeta('nonexistent'));
	}

	/**
	 * @covers \Blight\Models\Page::getLink
	 */
	public function testGetLink(){
		$url	= $this->blog->getURL($this->content_slug);
		$this->assertEquals($url, $this->page->getLink());
	}

	/**
	 * @covers \Blight\Models\Page::getPermalink
	 */
	public function testGetPermalink(){
		$url	= $this->blog->getURL($this->content_slug);
		$this->assertEquals($url, $this->page->getPermalink());
	}

	/**
	 * @covers \Blight\Models\Page::getRelativePermalink
	 */
	public function testGetRelativePermalink(){
		$url	= $this->content_slug;
		$this->assertEquals($url, $this->page->getRelativePermalink());
	}
};