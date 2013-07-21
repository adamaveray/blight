<?php
namespace Blight\Tests;

class FileSystemTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	protected $fileDir;
	protected $filePath;
	protected $fileContent;

	/** @var \Blight\Interfaces\FileSystem */
	protected $fileSystem;

	public function setUp(){
		global $config;
		$testConfig	= $config;
		$testConfig['paths']['templates']	= __DIR__.'/files/templates/';
		$this->blog		= new \Blight\Blog($testConfig);

		$this->fileDir		= __DIR__.'/files/';
		$this->filePath		= $this->fileDir.'test.txt';
		$this->fileContent	= 'Test content';

		$this->fileSystem	= new \Blight\FileSystem($this->blog);
	}

	/**
	 * @covers \Blight\FileSystem::__construct
	 */
	public function testConstruct(){
		$fileSystem	= new \Blight\FileSystem($this->blog);
		$this->assertInstanceOf('\Blight\FileSystem', $fileSystem);
	}

	/**
	 * @covers \Blight\FileSystem::createFile
	 */
	public function testCreateFile(){
		$this->assertFalse(file_exists($this->filePath));
		$this->fileSystem->createFile($this->filePath, $this->fileContent);
		$this->assertFileExists($this->filePath);
	}

	/**
	 * @covers \Blight\FileSystem::loadFile
	 * @depends testCreateFile
	 */
	public function testLoadFile(){
		$this->assertTrue(file_exists($this->filePath));
		$this->assertEquals($this->fileContent, $this->fileSystem->loadFile($this->filePath));
	}

	/**
	 * @covers \Blight\FileSystem::loadFile
	 * @expectedException \RuntimeException
	 */
	public function testInvalidLoadFile(){
		$path		= $this->fileDir.'nonexistent.txt';
		$this->fileSystem->loadFile($path);
	}

	/**
	 * @covers \Blight\FileSystem::copyFile
	 * @depends testCreateFile
	 */
	public function testCopyFile(){
		$newPath	= $this->fileDir.'copied.txt';
		$this->fileSystem->copyFile($this->filePath, $newPath);
		// New file exists
		$this->assertFileExists($newPath);
		// Old file exists
		$this->assertFileExists($this->filePath);
		// New file content matches old
		$this->assertEquals($this->fileContent, file_get_contents($newPath));
	}

	/**
	 * @covers \Blight\FileSystem::moveFile
	 * @depends testCreateFile
	 */
	public function testMoveFile(){
		$newPath	= $this->fileDir.'moved.txt';
		$this->fileSystem->moveFile($this->filePath, $newPath);
		// New file exists
		$this->assertFileExists($newPath);
		// Old file does not exist
		$this->assertFalse(file_exists($this->filePath));
		// New file content matches old
		$this->assertEquals($this->fileContent, file_get_contents($newPath));
	}

	/**
	 * @covers \Blight\FileSystem::deleteFile
	 */
	public function testDeleteFile(){
		$path	= $this->fileDir.'delete.txt';
		// Create file
		file_put_contents($path, $this->fileContent);

		$this->fileSystem->deleteFile($path);
		$this->assertFalse(file_exists($path));
	}

	/**
	 * @covers \Blight\FileSystem::createDir
	 */
	public function testCreateDir(){
		$path	= $this->fileDir.'dir';
		$this->fileSystem->createDir($path);
		$this->assertTrue(is_dir($path));
	}

	/**
	 * @covers \Blight\FileSystem::copyDir
	 * @depends testCreateDir
	 */
	public function testCopyDir(){
		$source	= $this->fileDir.'dir';
		$target	= $this->fileDir.'dir-2';

		$this->fileSystem->copyDir($source, $target);
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