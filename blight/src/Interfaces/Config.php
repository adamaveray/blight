<?php
namespace Blight\Interfaces;

interface Config {
	public function parse($contents);

	public function build($values);
};
