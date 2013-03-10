<?php
namespace Blight;

class Pagination implements \Blight\Interfaces\Pagination, \Iterator, \Countable, \ArrayAccess {
	protected $position;
	protected $items;

	/**
	 * @param array $items			The items to paginate
	 * @param int|null $position	The current item's position in the paginator (1-indexed)
	 *
	 * @throws \InvalidArgumentException	$items is not an array
	 * @throws \InvalidArgumentException	$items is empty
	 * @throws \InvalidArgumentException	$position does not exist in $items
	 */
	public function __construct($items, $position = null){
		if(!is_array($items)){
			throw new \InvalidArgumentException('Items must be array');
		} elseif(count($items) === 0){
			throw new \InvalidArgumentException('Items cannot be empty');
		}
		if(isset($items[0]) && !isset($items[count($items)])){
			// 0-indexed - convert
			$new_items	= array();
			foreach($items as $key => $item){
				$new_items[$key+1]	= $item;
			}
			$items	= $new_items;
		}

		if(!isset($position)){
			$position	= 1;
		} elseif(!is_numeric($position) || $position < 1 || $position > count($items)){
			throw new \InvalidArgumentException('Position "'.$position.'" does not exist in items '.count($items));
		}

		$this->items	= $items;
		$this->position	= $position;
	}

	/**
	 * Retrieves the item before the item at the current position
	 *
	 * @return mixed	The item before the current item
	 */
	public function get_prev(){
		return $this->get_index($this->position-1);
	}

	/**
	 * Retrieves the item after the item at the current position
	 *
	 * @return mixed	The item after the current item
	 */
	public function get_next(){
		return $this->get_index($this->position+1);
	}

	/**
	 * Returns the number of items provided during construction
	 *
	 * @return int	The number of items
	 */
	public function get_count(){
		return count($this->items);
	}

	/**
	 * Retrieves the item at the current position
	 *
	 * @return mixed	The current item
	 */
	public function get_current(){
		return $this->get_index($this->position);
	}

	/**
	 * Returns the position within the items, set during construction
	 *
	 * @return int	The current position
	 */
	public function get_position(){
		return $this->position;
	}

	/**
	 * Retrieves the item at the provided index
	 *
	 * @param int $i	The index of the item to retrieve
	 * @return mixed	The item at the given index
	 * @throws \OutOfRangeException	No item exists at the given position
	 */
	public function get_index($i){
		if($i < 1 || $i > $this->get_count()){
			throw new \OutOfRangeException('Invalid position requested');
		}

		return $this->items[$i];
	}


	// Iterator
	protected $iterator_position	= 1;

	public function rewind(){
		$this->iterator_position	= 1;
	}

	public function current(){
		return $this->items[$this->iterator_position];
	}

	public function key(){
		return $this->iterator_position;
	}

	public function next(){
		$this->iterator_position++;
	}

	public function valid(){
		return ($this->iterator_position >= 1 && $this->iterator_position <= $this->get_count());
	}


	// Array Access
	public function offsetSet($offset, $value){
		throw new \BadMethodCallException('Immutable object cannot be modified');
	}

	public function offsetUnset($offset){
		throw new \BadMethodCallException('Immutable object cannot be modified');
	}

	public function offsetExists($offset){
		return isset($this->items[$offset]);
	}

	public function offsetGet($offset){
		return isset($this->items[$offset]) ? $this->items[$offset] : null;
	}


	// Countable
	public function count(){
		return $this->get_count();
	}
};
