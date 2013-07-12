<?php
namespace Blight\Interfaces;

interface Pagination {
	/**
	 * @param array $items			The items to paginate
	 * @param int|null $position	The current item's position in the paginator (1-indexed)
	 *
	 * @throws \InvalidArgumentException	$items is not an array
	 * @throws \InvalidArgumentException	$items is empty
	 * @throws \InvalidArgumentException	$position does not exist in $items
	 */
	public function __construct($items, $position = null);

	/**
	 * @return mixed	The item before the current item
	 */
	public function getPrev();

	/**
	 * @return mixed	The item after the current item
	 */
	public function getNext();

	/**
	 * @return bool	Whether the paginated data has a previous item
	 */
	public function hasPrev();

	/**
	 * @return bool	Whether the paginated data has a next item
	 */
	public function hasNext();

	/**
	 * @return int	The number of items
	 */
	public function getCount();

	/**
	 * @return mixed	The current item
	 */
	public function getCurrent();

	/**
	 * @return int	The current position
	 */
	public function getPosition();

	/**
	 * @param int $i	The index of the item to retrieve (1-indexed)
	 * @param bool $isZeroIndexed	Whether the index requested is 0-indexed or 1-indexed
	 * @return mixed	The item at the given index
	 * @throws \OutOfRangeException	No item exists at the given position
	 */
	public function getIndex($i, $isZeroIndexed = false);
};
