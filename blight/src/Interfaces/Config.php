<?php
namespace Blight\Interfaces;

interface Config {
	/**
	 * @param array $values	The config data to convert
	 * @return string		The converted string config data
	 */
	public function serialize($contents);

	/**
	 * @param string $contents	The contents for the config file
	 * @return array	An associative array of the config data
	 */
	public function unserialize($values);
};
