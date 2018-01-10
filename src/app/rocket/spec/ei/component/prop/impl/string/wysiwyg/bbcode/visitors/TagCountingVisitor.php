<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\spec\ei\component\field\impl\string\wysiwyg\bbcode\visitors;

/**
 * This visitor traverses parse graph, counting the number of times each
 * tag name occurs.
 *
 * @author jbowens
 * @since January 2013
 */
use rocket\spec\ei\component\field\impl\string\wysiwyg\bbcode\ElementNode;

use rocket\spec\ei\component\field\impl\string\wysiwyg\bbcode\TextNode;

use rocket\spec\ei\component\field\impl\string\wysiwyg\bbcode\DocumentElement;

use rocket\spec\ei\component\field\impl\string\wysiwyg\bbcode\NodeVisitor;

class TagCountingVisitor implements NodeVisitor
{
	protected $frequencies = array();

	public function visitDocumentElement(DocumentElement $documentElement)
	{
		foreach ($documentElement->getChildren() as $child) {
			$child->accept($this);
		}
	}

	public function visitTextNode(TextNode $textNode)
	{
		// Nothing to do here, text nodes do not have tag names or children
	}

	public function visitElementNode(ElementNode $elementNode)
	{
		$tagName = strtolower($elementNode->getTagName());

		// Update this tag name's frequency
		if (isset($this->frequencies[$tagName])) {
			$this->frequencies[$tagName]++;
		} else {
			$this->frequencies[$tagName] = 1;
		}

		// Visit all the node's childrens
		foreach ($elementNode->getChildren() as $child) {
			$child->accept($this);
		}

	}

	/**
	 * Retrieves the frequency of the given tag name.
	 *
	 * @param $tagName  the tag name to look up
	 */
	public function getFrequency($tagName)
	{
		if (!isset($this->frequencies[$tagName])) {
			return 0;
		} else {
			return $this->frequencies[$tagName];
		}
	}

}
