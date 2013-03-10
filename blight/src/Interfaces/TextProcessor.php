<?php
namespace Blight\Interfaces;

interface TextProcessor {
	public function __construct(\Blight\Interfaces\Blog $blog);

	public function process($raw, $filters = null);

	public function process_markdown($raw);

	public function process_typography($html);

	public function truncate_html($html, $length = 100, $ending = '...');
};
