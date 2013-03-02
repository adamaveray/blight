<?php
namespace Blight\Collections;

class Tag extends Collection implements \Blight\Interfaces\Collection {
	public function get_url(){
		return $this->blog->get_url('tag/'.$this->get_slug());
	}
};