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
			$newItems	= array();
			foreach($items as $key => $item){
				$newItems[$key+1]	= $item;
			}
			$items	= $newItems;
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
	public function getPrev(){
		return $this->getIndex($this->position-1);
	}

	/**
	 * Retrieves the item after the item at the current position
	 *
	 * @return mixed	The item after the current item
	 */
	public function getNext(){
		return $this->getIndex($this->position+1);
	}

	/**
	 * Checks whether the paginated data has a previous item
	 *
	 * @return bool	Whether the paginated data has a previous item
	 */
	public function hasPrev(){
		return ($this->getPosition() > 1);
	}

	/**
	 * Checks whether the paginated data has a next item
	 *
	 * @return bool	Whether the paginated data has a next item
	 */
	public function hasNext(){
		return ($this->getPosition() < $this->getCount());
	}

	/**
	 * Returns the number of items provided during construction
	 *
	 * @return int	The number of items
	 */
	public function getCount(){
		return count($this->items);
	}

	/**
	 * Retrieves the item at the current position
	 *
	 * @return mixed	The current item
	 */
	public function getCurrent(){
		return $this->getIndex($this->position);
	}

	/**
	 * Returns the position within the items, set during construction
	 *
	 * @return int	The current position
	 */
	public function getPosition(){
		return $this->position;
	}

	/**
	 * Retrieves the item at the provided index
	 *
	 * @param int $i	The index of the item to retrieve (1-indexed)
	 * @param bool $isZeroIndexed	Whether the index requested is 0-indexed or 1-indexed
	 * @return mixed	The item at the given index
	 * @throws \OutOfRangeException	No item exists at the given position
	 */
	public function getIndex($i, $isZeroIndexed = false){
		if($isZeroIndexed){
			$i++;
		}

		if($i < 1 || $i > $this->getCount()){
			throw new \OutOfRangeException('Invalid position '.$i.' requested');
		}

		return $this->items[$i];
	}


	// Iterator
	protected $iteratorPosition	= 1;

	public function rewind(){
		$this->iteratorPosition	= 1;
	}

	public function current(){
		return $this->items[$this->iteratorPosition];
	}

	public function key(){
		return $this->iteratorPosition;
	}

	public function next(){
		$this->iteratorPosition++;
	}

	public function valid(){
		return ($this->iteratorPosition >= 1 && $this->iteratorPosition <= $this->getCount());
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
		return $this->getCount();
	}
};
