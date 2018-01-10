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
namespace rocket\spec\ei\component\field\impl\string\wysiwyg\bbcode;

/**
 * A DocumentElement object represents the root of a document tree. All documents represented by
 * this document model should have one as its root.
 *
 * @author jbowens
 */

class DocumentElement extends ElementNode
{
	/**
	 * Constructs the document element node
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setTagName("Document");
		$this->setNodeId(0);
	}

	/**
	 * (non-PHPdoc)
	 * @see JBBCode.ElementNode::getAsBBCode()
	 *
	 * Returns the BBCode representation of this document
	 *
	 * @return this document's bbcode representation
	 */
	public function getAsBBCode()
	{
		$s = "";
		foreach($this->getChildren() as $child)
			$s .= $child->getAsBBCode();

		return $s;
	}

	/**
	 * (non-PHPdoc)
	 * @see JBBCode.ElementNode::getAsHTML()
	 *
	 * Documents don't add any html. They only exist as a container for their children, so getAsHTML() simply iterates through the
	 * document's children, returning their html.
	 *
	 * @return the HTML representation of this document
	 */
	public function getAsHTML()
	{
		$s = "";
		foreach($this->getChildren() as $child)
			$s .= $child->getAsHTML();

		return $s;
	}

	public function accept(NodeVisitor $visitor)
	{
		$visitor->visitDocumentElement($this);
	}

}
