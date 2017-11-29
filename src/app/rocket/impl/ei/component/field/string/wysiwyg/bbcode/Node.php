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
namespace rocket\impl\ei\component\field\string\wysiwyg\bbcode;

/**
 * A node within the document tree.
 *
 * Known subclasses: TextNode, ElementNode
 *
 * @author jbowens
 */
abstract class Node
{
	/* Pointer to the parent node of this node */
	protected $parent;

	/* The node id of this node */
	protected $nodeid;

	/**
	 * Returns the node id of this node. (Not really ever used. Dependent upon the parse tree the node exists within.)
	 *
	 * @return this node's id
	 */
	public function getNodeId()
	{
		return $this->nodeid;
	}

	/**
	 * Returns this node's immediate parent.
	 *
	 * @return the node's parent
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Determines if this node has a parent.
	 *
	 * @return true if this node has a parent, false otherwise
	 */
	public function hasParent()
	{
		return $this->parent != null;
	}

	/**
	 * Returns true if this is a text node. Returns false otherwise.
	 * (Overridden by TextNode to return true)
	 *
	 * @return true if this node is a text node
	 */
	public function isTextNode()
	{
		return false;
	}

	/**
	 * Accepts a NodeVisitor
	 *
	 * @param nodeVisitor  the NodeVisitor traversing the graph
	 */
	abstract public function accept(NodeVisitor $nodeVisitor);

	/**
	 * Returns this node as text (without any bbcode markup)
	 *
	 * @return the plain text representation of this node
	 */
	abstract public function getAsText();

	/**
	 * Returns this node as bbcode
	 *
	 * @return the bbcode representation of this node
	 */
	abstract public function getAsBBCode();

	/**
	 * Returns this node as HTML
	 *
	 * @return the html representation of this node
	 */
	abstract public function getAsHTML();

	/**
	 * Sets this node's parent to be the given node.
	 *
	 * @param parent the node to set as this node's parent
	 */
	public function setParent(Node $parent)
	{
		$this->parent = $parent;
	}

	/**
	 * Sets this node's nodeid
	 *
	 * @param nodeid this node's node id
	 */
	public function setNodeId($nodeid)
	{
		$this->nodeid = $nodeid;
	}

}
