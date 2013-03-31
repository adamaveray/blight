<?php
namespace Blight\Interfaces\Models;

interface Author {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @param array $data
	 *
	 * 		$data	= array(
	 * 			'name'	=> '',	// Required
	 *			'email'	=> '',
	 *			'url'	=> ''
	 * 		)
	 *
	 * @throws \InvalidArgumentException	The data is missing the name
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $data);

	/**
	 * @return string	The author's name
	 */
	public function getName();

	/**
	 * @return string	The author's email address
	 * @throws \RuntimeException	The author does not have an email address set
	 */
	public function getEmail();

	/**
	 * @return string	The author's URL
	 * @throws \RuntimeException	The author does not have a URL set
	 */
	public function getURL();

	/**
	 * @param string $name	The name of the attribute to retrieve
	 * @return mixed		The attribute value
	 * @throws \RuntimeException	The author does not have the requested attribute set
	 */
	public function getAttribute($name);

	/**
	 * @return bool	Whether the author has an email address set
	 */
	public function hasEmail();

	/**
	 * @return bool	Whether the author has a URL set
	 */
	public function hasURL();

	/**
	 * @param string $name	The name of the attribute to check for
	 * @return bool	Whether the attribute exists
	 */
	public function hasAttribute($name);
};
