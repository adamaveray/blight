<?php
namespace Blight\Tests;

class PaginationTest extends \PHPUnit_Framework_TestCase {
	protected $items;

	public function setUp(){
		$this->items	= array(
			1	=> 'Item 1',
			2	=> 'Item 2',
			3	=> 'Item 3'
		);
	}

	/**
	 * @covers \Blight\Pagination::__construct
	 */
	public function testConstruct(){
		$pagination	= new \Blight\Pagination($this->items);

		$this->assertInstanceOf('\Blight\Pagination', $pagination);
	}

	/**
	 * @covers \Blight\Pagination::__construct
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidStartConstruct(){
		new \Blight\Pagination($this->items, count($this->items)+1);
	}

	/**
	 * @covers \Blight\Pagination::__construct
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidItemsConstruct(){
		$items	= '(not an array)';
		new \Blight\Pagination($items);
	}

	/**
	 * @covers \Blight\Pagination::__construct
	 * @expectedException \InvalidArgumentException
	 */
	public function testEmptyItemsConstruct(){
		$items	= array();
		new \Blight\Pagination($items);
	}

	/**
	 * @covers \Blight\Pagination::get_prev
	 */
	public function testGetPrev(){
		$start	= 2;
		$pagination	= new \Blight\Pagination($this->items, $start);

		$this->assertEquals($this->items[$start-1], $pagination->get_prev());
	}

	/**
	 * @covers \Blight\Pagination::get_prev
	 * @expectedException \OutOfRangeException
	 */
	public function testInvalidGetPrev(){
		$start	= 1;
		$pagination	= new \Blight\Pagination($this->items, $start);

		$pagination->get_prev();
	}

	/**
	 * @covers \Blight\Pagination::get_next
	 */
	public function testGetNext(){
		$start	= 2;
		$pagination	= new \Blight\Pagination($this->items, $start);

		$this->assertEquals($this->items[$start+1], $pagination->get_next());
	}

	/**
	 * @covers \Blight\Pagination::get_next
	 * @expectedException \OutOfRangeException
	 */
	public function testInvalidGetNext(){
		// Final item
		$start	= count($this->items);
		$pagination	= new \Blight\Pagination($this->items, $start);

		$pagination->get_next();
	}

	/**
	 * @covers \Blight\Pagination::get_count
	 */
	public function testGetCount(){
		$pagination	= new \Blight\Pagination($this->items);

		$this->assertEquals(count($this->items), $pagination->get_count());
	}

	/**
	 * @covers \Blight\Pagination::get_current
	 */
	public function testGetCurrent(){
		$start	= 1;
		$pagination	= new \Blight\Pagination($this->items, $start);

		$this->assertEquals($this->items[$start], $pagination->get_current());
	}

	/**
	 * @covers \Blight\Pagination::get_next
	 */
	public function testGetPosition(){
		$start	= 1;
		$pagination	= new \Blight\Pagination($this->items, $start);

		$this->assertEquals($start, $pagination->get_position());
	}

	/**
	 * @covers \Blight\Pagination::get_index
	 */
	public function testGetIndex(){
		$pagination	= new \Blight\Pagination($this->items);

		$i	= 1;
		$this->assertEquals($this->items[$i], $pagination->get_index($i));
	}

	/**
	 * @covers \Blight\Pagination::get_index
	 * @expectedException \OutOfRangeException
	 */
	public function testInvalidGetIndex(){
		$pagination	= new \Blight\Pagination($this->items);

		$pagination->get_index(count($this->items)+1);
	}

	/**
	 * @covers \Blight\Pagination::rewind
	 * @covers \Blight\Pagination::current
	 * @covers \Blight\Pagination::key
	 * @covers \Blight\Pagination::next
	 * @covers \Blight\Pagination::valid
	 */
	public function testIterator(){
		$pagination	= new \Blight\Pagination($this->items);

		$result	= array();
		foreach($pagination as $key => $item){
			$result[$key]	= $item;
		}

		$this->assertEquals($this->items, $result);
	}

	/**
	 * @covers \Blight\Pagination::offsetExists
	 * @covers \Blight\Pagination::offsetGet
	 */
	public function testArrayAccess(){
		$pagination	= new \Blight\Pagination($this->items);

		$this->assertEquals($this->items[1], $pagination[1]);
		$this->assertEquals($this->items[2], $pagination[2]);

		$this->assertTrue(isset($pagination[1]));
		$this->assertFalse(isset($pagination[count($this->items)+1]));
	}

	/**
	 * @covers \Blight\Pagination::offsetSet
	 * @expectedException \BadMethodCallException
	 */
	public function testInvalidSetArrayAccess(){
		$pagination	= new \Blight\Pagination($this->items);

		$pagination[]	= 'Item';
	}

	/**
	 * @covers \Blight\Pagination::offsetSet
	 * @expectedException \BadMethodCallException
	 */
	public function testInvalidUnsetArrayAccess(){
		$pagination	= new \Blight\Pagination($this->items);

		unset($pagination[0]);
	}

	/**
	 * @covers \Blight\Pagination::count
	 */
	public function testCountable(){
		$pagination	= new \Blight\Pagination($this->items);

		$this->assertEquals(count($this->items), count($pagination));
	}
};