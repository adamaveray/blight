<?php
namespace Blight\Interfaces;

interface Config {
	public function serialize($contents);

	public function unserialize($values);
};
