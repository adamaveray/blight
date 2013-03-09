<?php
namespace Blight\Tests;

class FileSystemTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	protected $file_dir;
	protected $file_path;
	protected $file_content;

	/** @var \Blight\Interfaces\FileSystem */
	protected $file_system;

	public function setUp(){
		global $config;
		$test_config	= $config;
		$test_config['paths']['templates']	= __DIR__.'/files/templates/';
		$this->blog		= new \Blight\Blog($test_config);

		$this->file_dir		= __DIR__.'/files/';
		$this->file_path	= $this->file_dir.'test.txt';
		$this->file_content	= 'Test content';

		$this->file_system	= new \Blight\FileSystem($this->blog);
	}

	/**
	 * @covers \Blight\FileSystem::__construct
	 */
	public function testConstruct(){
		$file_system	= new \Blight\FileSystem($this->blog);
		$this->assertInstanceOf('\Blight\FileSystem', $file_system);
	}

	/**
	 * @covers \Blight\FileSystem::create_file
	 */
	public function testCreateFile(){
		$this->assertFalse(file_exists($this->file_path));
		$this->file_system->create_file($this->file_path, $this->file_content);
		$this->assertFileExists($this->file_path);
	}

	/**
	 * @covers \Blight\FileSystem::load_file
	 * @depends testCreateFile
	 */
	public function testLoadFile(){
		$this->assertTrue(file_exists($this->file_path));
		$this->assertEquals($this->file_system->load_file($this->file_path), $this->file_content);
	}

	/**
	 * @covers \Blight\FileSystem::load_file
	 * @expectedException \RuntimeException
	 */
	public function testInvalidLoadFile(){
		$path		= $this->file_dir.'nonexistent.txt';
		$this->file_system->load_file($path);
	}

	/**
	 * @covers \Blight\FileSystem::copy_file
	 * @depends testCreateFile
	 */
	public function testCopyFile(){
		$new_path	= $this->file_dir.'copied.txt';
		$this->file_system->copy_file($this->file_path, $new_path);
		// New file exists
		$this->assertFileExists($new_path);
		// Old file exists
		$this->assertFileExists($this->file_path);
		// New file content matches old
		$this->assertEquals(file_get_contents($new_path), $this->file_content);
	}

	/**
	 * @covers \Blight\FileSystem::move_file
	 * @depends testCreateFile
	 */
	public function testMoveFile(){
		$new_path	= $this->file_dir.'moved.txt';
		$this->file_system->move_file($this->file_path, $new_path);
		// New file exists
		$this->assertFileExists($new_path);
		// Old file does not exist
		$this->assertFalse(file_exists($this->file_path));
		// New file content matches old
		$this->assertEquals(file_get_contents($new_path), $this->file_content);
	}

	/**
	 * @covers \Blight\FileSystem::delete_file
	 */
	public function testDeleteFile(){
		$path	= $this->file_dir.'delete.txt';
		// Create file
		file_put_contents($path, $this->file_content);

		$this->file_system->delete_file($path);
		$this->assertFalse(file_exists($path));
	}

	/**
	 * @covers \Blight\FileSystem::create_dir
	 */
	public function testCreateDir(){
		$path	= $this->file_dir.'dir';
		$this->file_system->create_dir($path);
		$this->assertTrue(is_dir($path));
	}

	/**
	 * @covers \Blight\FileSystem::copy_dir
	 * @depends testCreateDir
	 */
	public function testCopyDir(){
		$source	= $this->file_dir.'dir';
		$target	= $this->file_dir.'dir-2';

		$this->file_system->copy_dir($source, $target);
		// Old directory exists
		$this->assertTrue(is_dir($source));
		// New directory exists
		$this->assertTrue(is_dir($target));
	}


	static public function tearDownAfterClass(){
		$path	= __DIR__.'/files/';
		$files	= array(
			'test.txt',
			'copied.txt',
			'moved.txt',
			'delete.txt'
		);
		foreach($files as $file){
			if(file_exists($path.$file)){
				unlink($path.$file);
			}
		}
		$dirs	= array(
			'dir',
			'dir-2'
		);
		foreach($dirs as $dir){
			if(is_dir($path.$dir)){
				rmdir($path.$dir);
			}
		}
	}
};