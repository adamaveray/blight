<?php
namespace Blight\Tests;

class PostTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	protected $contentTitle;
	protected $contentDate;
	protected $contentSlug;
	protected $contentText;
	protected $contentTags;
	protected $contentCategory;
	protected $contentMetadata;
	protected $content;
	protected $linkedContent;
	protected $linkedContentLink;

	/** @var \Blight\Interfaces\Models\Post */
	protected $post;

	public function setUp(){
		global $config;
		$this->blog	= new \Blight\Blog($config);

		$this->contentTitle	= 'Test Post';
		$this->contentSlug		= 'test-post';
		$this->contentDate		= new \DateTime();
		$this->contentTags		= array(
			'Test Tag',
			'Other Tag'
		);
		$this->contentCategory	= 'General';

		$this->contentMetadata	= array(
			'Date'		=> $this->contentDate->format('Y-m-d H:i:s'),
			'Test Meta'	=> 'Test Value',
			'Tags'		=> implode(', ', $this->contentTags),
			'Category'	=> $this->contentCategory
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

		$this->post	= new \Blight\Models\Post($this->blog, $this->content, $this->contentSlug);

		$this->linkedContentLink	= 'http://www.example.com/';
		$this->linkedContent	= <<<EOD
$this->contentTitle
=========
Link: $this->linkedContentLink
$meta

Test Content.
EOD;
	}

	/**
	 * @covers \Blight\Models\Post::__construct
	 */
	public function testConstruct(){
		$post	= new \Blight\Models\Post($this->blog, $this->content, $this->contentSlug);
		$this->assertInstanceOf('\Blight\Models\Post', $post);
	}

	/**
	 * @covers \Blight\Models\Post::__construct
	 */
	public function testDraftConstruct(){
		$post	= new \Blight\Models\Post($this->blog, $this->content, $this->contentSlug, true);
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
		$this->assertEquals($this->contentTitle, $this->post->getTitle(true));


		// Test linkblogs

		global $config;
		$testConfig	= $config;

		// Test non-linkblog
		$linkChar	= '>';
		$testConfig['linkblog']['linkblog']		= false;
		$testConfig['linkblog']['link_character']	= $linkChar;
		$blog	= new \Blight\Blog($testConfig);
		// Non-linked post
		$post	= new \Blight\Models\Post($blog, $this->content, $this->contentSlug);
		$this->assertEquals($this->contentTitle, $post->getTitle());
		// Linked post
		$post	= new \Blight\Models\Post($blog, $this->linkedContent, $this->contentSlug);
		$this->assertEquals($linkChar.' '.$this->contentTitle, $post->getTitle());

		// Test linkblog
		$postChar	= '*';
		$testConfig['linkblog']['linkblog']		= true;
		$testConfig['linkblog']['post_character']	= $postChar;
		$blog	= new \Blight\Blog($testConfig);
		// Non-linked post
		$post	= new \Blight\Models\Post($blog, $this->content, $this->contentSlug);
		$this->assertEquals($postChar.' '.$this->contentTitle, $post->getTitle());
		// Linked post
		$post	= new \Blight\Models\Post($blog, $this->linkedContent, $this->contentSlug);
		$this->assertEquals($this->contentTitle, $post->getTitle());
	}

	/**
	 * @covers \Blight\Models\Post::getMetadata
	 */
	public function testGetMetadata(){
		$meta	= array(
			'date'		=> $this->contentMetadata['Date'],
			'test-meta'	=> $this->contentMetadata['Test Meta'],
			'tags'		=> $this->contentMetadata['Tags'],
			'category'	=> $this->contentMetadata['Category']
		);

		$this->assertEquals($meta, $this->post->getMetadata());
	}

	/**
	 * @covers \Blight\Models\Post::getLink
	 */
	public function testGetLink(){
		$url	= $this->blog->getURL($this->contentDate->format('Y/m').'/'.$this->contentSlug);
		$this->assertEquals($url, $this->post->getLink());

		// Test linked post
		$post	= new \Blight\Models\Post($this->blog, $this->linkedContent, $this->contentSlug);
		$this->assertEquals($this->linkedContentLink, $post->getLink());
	}

	/**
	 * @covers \Blight\Models\Post::getPermalink
	 */
	public function testGetPermalink(){
		$url	= $this->blog->getURL($this->contentDate->format('Y/m').'/'.$this->contentSlug);
		$this->assertEquals($url, $this->post->getPermalink());
		$this->assertEquals($this->post->getLink(), $this->post->getPermalink());

		// Test linked post
		$post	= new \Blight\Models\Post($this->blog, $this->linkedContent, $this->contentSlug);
		$url	= $this->blog->getURL($this->contentDate->format('Y/m').'/'.$this->contentSlug);
		$this->assertEquals($url, $post->getPermalink());
		$this->assertNotEquals($post->getLink(), $post->getPermalink());
	}

	/**
	 * @covers \Blight\Models\Post::getRelativePermalink
	 */
	public function testGetRelativePermalink(){
		$url	= $this->contentDate->format('Y/m').'/'.$this->contentSlug;
		$this->assertEquals($url, $this->post->getRelativePermalink());

		// Test linked post
		$post	= new \Blight\Models\Post($this->blog, $this->linkedContent, $this->contentSlug);
		$url	= $this->contentDate->format('Y/m').'/'.$this->contentSlug;
		$this->assertEquals($url, $post->getRelativePermalink());
	}

	/**
	 * @covers \Blight\Models\Post::getYear
	 */
	public function testGetYear(){
		$year	= $this->post->getYear();
		$this->assertInstanceOf('\Blight\Models\Collections\Year', $year);

		$this->assertEquals($this->contentDate->format('Y'), $year->getName());
	}

	/**
	 * @covers \Blight\Models\Post::getTags
	 */
	public function testGetTags(){
		$tags	= $this->post->getTags();
		$this->assertTrue(is_array($tags));
		$this->assertEquals(count($this->contentTags), count($tags));

		foreach($tags as $tag){
			$this->assertInstanceOf('\Blight\Models\Collections\Tag', $tag);
			$this->assertTrue(in_array($tag->getName(), $this->contentTags));
		}
	}

	/**
	 * @covers \Blight\Models\Post::getTags
	 */
	public function testDuplicateGetTags(){
		$rawTags	= array(
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

		$post	= new \Blight\Models\Post($this->blog, str_replace('{TAGS}', implode(',', $rawTags), $content), 'test-post');
		$tags	= $post->getTags();
		$this->assertTrue(is_array($tags));
		$this->assertNotEquals(count($rawTags), count($tags));
		$this->assertEquals(count($rawTags)-1, count($tags));

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

		$this->assertEquals($this->contentCategory, current($categories)->getName());
	}

	/**
	 * @covers \Blight\Models\Post::hasSummary
	 */
	public function testHasSummary(){
		$this->assertTrue($this->post->hasSummary());

		// Test with summary header
		$summary	= 'A test summary';
		$content	= $this->content;
		$content	= preg_replace('/(=+)(\n)/', '$1$2Summary: '.$summary.'$2', $content);

		$post	= new \Blight\Models\Post($this->blog, $content, 'test-post');
		$this->assertTrue($post->hasSummary());

		// Test disabling generated summary
		global $config;
		$summaryConfig	= $config;
		$summaryConfig['output']['generate_summaries']	= false;
		$alternateBlog	= new \Blight\Blog($summaryConfig);

		$content	= $this->content;
		$post	= new \Blight\Models\Post($alternateBlog, $content, 'test-post');
		$this->assertFalse($post->hasSummary());
	}

	/**
	 * @covers \Blight\Models\Post::getSummary
	 */
	public function testGetSummary(){
		$alternateText	= 'Aenean ullamcorper nisi lorem, non egestas metus posuere id. Donec vitae gravida dolor, sit amet lobortis magna. Mauris mi leo, pellentesque sed hendrerit sit amet, mattis id erat. Mauris feugiat ullamcorper risus, eu facilisis enim pharetra at. Maecenas quis faucibus enim, ac pretium sapien. Integer pharetra ante nec lectus posuere tincidunt. Cras lobortis felis at eros blandit, eu viverra justo egestas. Sed eu tellus fringilla, molestie nunc ut, auctor ligula. Fusce enim nisi, volutpat sed neque eget, gravida gravida nulla. Pellentesque mattis mauris eget mi tempor lacinia.';

		$content	= str_replace($this->contentText, $alternateText, $this->content);
		$post	= new \Blight\Models\Post($this->blog, $content, 'test-post');
		$this->assertEquals($alternateText, $post->getSummary());
		$this->assertLessThanOrEqual(100, strlen($post->getSummary(100)));

		// Test with summary header
		$summary	= 'A test summary';
		$content	= $this->content;
		$content	= preg_replace('/(=+)(\n)/', '$1$2Summary: '.$summary.'$2', $content);

		$post	= new \Blight\Models\Post($this->blog, $content, 'test-post');
		$this->assertEquals($summary, $post->getSummary());

		// Test with generated summary
		global $config;
		$summaryConfig	= $config;
		$summaryConfig['output']['generate_summaries']	= false;
		$alternateBlog	= new \Blight\Blog($summaryConfig);

		$content	= $this->content;
		$post	= new \Blight\Models\Post($alternateBlog, $content, 'test-post');
		$this->assertNull($post->getSummary());
	}

	/**
	 * @covers \Blight\Models\Post::getAuthor
	 */
	public function testGetAuthor(){
		global $config;

		// No author
		$blog	= new \Blight\Blog($config);
		$blog->setAuthors(array());

		$post	= new \Blight\Models\Post($blog, $this->content, $this->contentSlug);
		$this->assertNull($post->getAuthor());

		// Site author
		$authorName	= 'Test Author';
		$alternateConfig	= $config;
		$alternateConfig['author']	= $authorName;
		$blog	= new \Blight\Blog($alternateConfig);
		$author	= new \Blight\Models\Author($blog, array(
			'name'	=> $authorName
		));
		$blog->setAuthors(array($author));

		$post	= new \Blight\Models\Post($blog, $this->content, $this->contentSlug);
		$this->assertEquals($author, $post->getAuthor());
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

		$post	= new \Blight\Models\Post($this->blog, $this->content, $this->contentSlug, true);
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

		$post	= new \Blight\Models\Post($this->blog, $this->linkedContent, $this->contentSlug);
		$this->assertTrue($post->isLinked());
	}

	/**
	 * @covers \Blight\Models\Post::getImages
	 */
	public function testGetImages(){
		$imageText	= 'Image Text';
		$imageURL	= '/img/image-url.jpg';
		$imageTitle	= 'Image Title';
		$content	= <<<EOD
Test Post
=========
Date: 2013-01-01

Test content

![${imageText}](${imageURL})

![${imageText} 2](${imageURL}-2)

![${imageText} 3](${imageURL}-3 "${imageTitle}")
EOD;
		$post	= new \Blight\Models\Post($this->blog, $content, 'test-post');
		$images	= $post->getImages();
		$this->assertTrue(is_array($images));
		$this->assertCount(3, $images);

		$this->assertEquals($imageText, $images[0]->getText());
		$this->assertEquals($imageURL, $images[0]->getURL());
		$this->assertEquals($this->blog->getURL($images[0]->getURL()), $images[0]->getURL(true));

		// Test additional
		$this->assertEquals($imageText.' 2', $images[1]->getText());

		// Test titles
		$this->assertFalse($images[0]->hasTitle());
		$this->assertTrue($images[2]->hasTitle());
		$this->assertEquals($imageTitle, $images[2]->getTitle());
	}
};