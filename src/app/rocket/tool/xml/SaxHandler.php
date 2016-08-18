<?php
namespace rocket\tool\xml;

interface SaxHandler {
	/**
	 *
	 * @param string $tagName
	 * @param array $attributes
	 */
	public function startElement($tagName, array $attributes);
	/**
	 *
	 * @param unknown_type $cdata
	*/
	public function cdata($cdata);
	/**
	 *
	 * @param unknown_type $tag
	*/
	public function endElement($tagName);
}
