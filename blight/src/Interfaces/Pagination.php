<?php
namespace Blight\Interfaces;

interface Pagination {
	public function __construct($items, $position = null);

	public function get_prev();

	public function get_next();

	public function get_count();

	public function get_current();

	public function get_index($i);
};
