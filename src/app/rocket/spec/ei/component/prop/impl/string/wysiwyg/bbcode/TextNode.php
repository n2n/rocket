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
 * Represents a piece of text data. TextNodes never have children.
 *
 * @author jbowens
 */
class TextNode extends Node
{
	/* The value of this text node */
	protected $value;

	/**
	 * Constructs a text node from its text string
	 *
	 * @param string $val
	 */
	public function __construct($val)
	{
		$this->value = $val;
	}

	public function accept(NodeVisitor $visitor)
	{
		$visitor->visitTextNode($this);
	}

	/**
	 * (non-PHPdoc)
	 * @see JBBCode.Node::isTextNode()
	 *
	 * returns true
	 */
	public function isTextNode()
	{
		return true;
	}

	/**
	 * Returns the text string value of this text node.
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * (non-PHPdoc)
	 * @see JBBCode.Node::getAsText()
	 *
	 * Returns the text representation of this node.
	 *
	 * @return this node represented as text
	 */
	public function getAsText()
	{
		return $this->getValue();
	}

	/**
	 * (non-PHPdoc)
	 * @see JBBCode.Node::getAsBBCode()
	 *
	 * Returns the bbcode representation of this node. (Just its value)
	 *
	 * @return this node represented as bbcode
	 */
	public function getAsBBCode()
	{
		return $this->getValue();
	}

	/**
	 * (non-PHPdoc)
	 * @see JBBCode.Node::getAsHTML()
	 *
	 * Returns the html representation of this node. (Just its value)
	 *
	 * @return this node represented as HTML
	 */
	public function getAsHTML()
	{
		return $this->getValue();
	}

	/**
	 * Edits the text value contained within this text node.
	 *
	 * @param newValue  the new text value of the text node
	 */
	public function setValue($newValue)
	{
		$this->value = $newValue;
	}

}
