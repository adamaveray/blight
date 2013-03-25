<?php
namespace Blight\Collections;

class Year extends Collection implements \Blight\Interfaces\Collection {
	public function getURL(){
		return $this->blog->getURL('archive/'.$this->getSlug());
	}
};
