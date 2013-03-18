<?php
namespace Blight\Interfaces;

interface TextProcessor {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 */
	public function __construct(\Blight\Interfaces\Blog $blog);

	/**
	 * @param string $raw	The text to process
	 * @param array|null $filters	An array of filters to enable or disable
	 *
	 * 		'markdown'
	 *
	 * 		'typography'
	 *
	 * @return string	The processed text
	 * @see process_markdown()
	 * @see process_typography()
	 */
	public function process($raw, $filters = null);

	/**
	 * @param string $raw	The raw Markdown
	 * @return string		The processed HTML
	 */
	public function process_markdown($raw);

	/**
	 * @param string $html	The HTML to process
	 * @return string		The processed HTML
	 */
	public function process_typography($html);

	/**
	 * @param string $html	The HTML to truncate
	 * @param int $length	The maximum length of text to return. If the given HTML is shorter than this length,
	 * 						no truncation will take place.
	 * @param string $ending	Characters to be appended if the string is truncated
	 * @return string	The truncated string
	 */
	public function truncate_html($html, $length = 100, $ending = '...');
};
