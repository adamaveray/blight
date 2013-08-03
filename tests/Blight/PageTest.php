<?php
namespace Blight\Tests;

class PageTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	protected $contentTitle;
	protected $contentDate;
	protected $contentSlug;
	protected $contentText;
	protected $contentMetadata;
	protected $content;

	protected $timezone;

	/** @var \Blight\Interfaces\Models\Page */
	protected $page;

	public function setUp(){
		global $config;
		$this->timezone	= new \DateTimezone($config['site']['timezone']);

		$this->blog	= new \Blight\Blog($config);

		$this->contentTitle	= 'Test Page';
		$this->contentSlug		= 'test-page';
		$this->contentDate		= new \DateTime('now', $this->timezone);
		$this->contentMetadata	= array(
			'Date'		=> $this->contentDate->format('Y-m-d H:i:s'),
			'Test Meta'	=> 'Test Value'
		);

		$meta	= array();
		foreach($this->contentMetadata as $key => $value){
			$meta[]	= $key.': '.$value;
		}
		$meta	= implode("\n", $meta);

		$this->contentText		= 'Test content.';

		$this->content	= <<<EOD
$this->contentTitle
=========
$meta

$this->contentText
EOD;

		$this->page	= new \Blight\Models\Page($this->blog, $this->content, $this->contentSlug);
	}

	/**
	 * @covers \Blight\Models\Page::__construct
	 */
	public function testConstruct(){
		$post	= new \Blight\Models\Page($this->blog, $this->content, $this->contentSlug);
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
		$this->assertEquals($this->contentTitle, $this->page->getTitle(true));
	}

	/**
	 * @covers \Blight\Models\Page::getSlug
	 */
	public function testGetSlug(){
		$this->assertEquals($this->contentSlug, $this->page->getSlug());
	}

	/**
	 * @covers \Blight\Models\Page::getDate
	 */
	public function testGetDate(){
		$this->assertEquals($this->contentDate, $this->page->getDate());
	}

	/**
	 * @covers \Blight\Models\Page::setDate
	 */
	public function testSetDate(){
		$date	= new \DateTime('now', $this->timezone);
		$this->page->setDate($date);
		$this->assertEquals($date, $this->page->getDate());
	}

	/**
	 * @covers \Blight\Models\Page::getDateUpdated
	 */
	public function testGetDateUpdated(){
		// Should default to date created
		$this->assertEquals($this->contentDate, $this->page->getDateUpdated());
	}

	/**
	 * @covers \Blight\Models\Page::setDateUpdated
	 */
	public function testSetDateUpdated(){
		$date	= new \DateTime('now', $this->timezone);
		$this->page->setDateUpdated($date);
		$this->assertEquals($date, $this->page->getDateUpdated());
	}

	/**
	 * @covers \Blight\Models\Page::getContent
	 */
	public function testGetContent(){
		$this->assertEquals($this->contentText, $this->page->getContent());
	}

	/**
	 * @covers \Blight\Models\Page::getMetadata
	 */
	public function testGetMetadata(){
		$meta	= array(
			'date'		=> $this->contentMetadata['Date'],
			'test-meta'	=> $this->contentMetadata['Test Meta']
		);

		$this->assertEquals($meta, $this->page->getMetadata());
	}

	/**
	 * @covers \Blight\Models\Page::getMeta
	 */
	public function testGetMeta(){
		$this->assertEquals($this->contentMetadata['Test Meta'], $this->page->getMeta('Test Meta'));
		$this->assertEquals($this->contentMetadata['Test Meta'], $this->page->getMeta('test-meta'));

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
		$url	= $this->blog->getURL($this->contentSlug);
		$this->assertEquals($url, $this->page->getLink());
	}

	/**
	 * @covers \Blight\Models\Page::getPermalink
	 */
	public function testGetPermalink(){
		$url	= $this->blog->getURL($this->contentSlug);
		$this->assertEquals($url, $this->page->getPermalink());
	}

	/**
	 * @covers \Blight\Models\Page::getRelativePermalink
	 */
	public function testGetRelativePermalink(){
		$url	= $this->contentSlug;
		$this->assertEquals($url, $this->page->getRelativePermalink());
	}
};