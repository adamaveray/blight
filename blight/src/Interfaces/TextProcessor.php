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
	 * @see processMarkdown()
	 * @see processTypography()
	 */
	public function process($raw, $filters = null);

	/**
	 * @param string $raw	The raw Markdown
	 * @return string		The processed HTML
	 */
	public function processMarkdown($raw);

	/**
	 * @param string $html	The HTML to process
	 * @return string		The processed HTML
	 */
	public function processTypography($html);

	/**
	 * Shortens a given block of text, preserving HTML tags and whole words
	 *
	 * @param string $html	The HTML to truncate
	 * @param int $length	The maximum length of text to return. If the given HTML is shorter than this length,
	 * 						no truncation will take place.
	 * @param string $ending	Characters to be appended if the string is truncated
	 * @param boolean $splitWords		Whether to truncate text mid-word
	 * @param boolean $handleHTML	If true, HTML tags would be handled correctly
	 *
	 * @return string	The truncated text
	 */
	public function truncateHTML($html, $length = 100, $ending = '…', $splitWords = false, $handleHTML = true);

	/**
	 * Minifies the provided HTML by removing whitespace, etc
	 *
	 * @param string $html	The raw HTML to minify
	 * @return string		The minified HTML
	 */
	public function minifyHTML($html);

	/**
	 * @param string $slug	The string to convert
	 * @return string		The converted string
	 *
	 * @see \Blight\Interfaces\Utilities::convertStringToSlug
	 */
	public function convertStringToSlug($string);
};
