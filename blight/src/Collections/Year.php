<?php
namespace Blight\Collections;

class Year extends Collection implements \Blight\Interfaces\Collection {
	public function get_url(){
		return $this->blog->get_url('archive/'.$this->get_slug());
	}
};
