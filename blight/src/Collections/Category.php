<?php
namespace Blight\Collections;

class Tag extends Collection implements \Blight\Interfaces\Collection {
	public function get_url(){
		return $this->blog->get_url('category/'.$this->get_slug());
	}
};