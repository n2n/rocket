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
namespace rocket\impl\ei\component\prop\string\wysiwyg\bbcode;

/**
 * Implements the builder pattern for the CodeDefinition class. A builder
 * is the recommended way of constructing CodeDefinition objects.
 *
 * @author jbowens
 */
class CodeDefinitionBuilder
{

	protected $tagName;
	protected $useOption = false;
	protected $replacementText;
	protected $parseContent = true;
	protected $nestLimit = -1;
	protected $optionValidator = null;
	protected $bodyValidator = null;

	/**
	 * Construct a CodeDefinitionBuilder.
	 *
	 * @param $tagName  the tag name of the definition to build
	 * @param $replacementText  the replacement text of the definition to build
	 */
	public function __construct($tagName, $replacementText)
	{
		$this->tagName = $tagName;
		$this->replacementText = $replacementText;
	}

	/**
	 * Sets the tag name the CodeDefinition should be built with.
	 *
	 * @param $tagName  the tag name for the new CodeDefinition
	 */
	public function setTagName($tagName)
	{
		$this->tagName = $tagName;
		return $this;
	}

	/**
	 * Sets the replacement text that the new CodeDefinition should be
	 * built with.
	 *
	 * @param $replacementText  the replacement text for the new CodeDefinition
	 */
	public function setReplacementText($replacementText)
	{
		$this->replacementText = $replacementText;
		return $this;
	}

	/**
	 * Set whether or not the built CodeDefinition should use the {option} bbcode
	 * argument.
	 *
	 * @param $option  true if the definition includes an option
	 */
	public function setUseOption($option)
	{
		$this->useOption = $option;
		return $this;
	}

	/**
	 * Set whether or not the built CodeDefinition should allow its content
	 * to be parsed and evaluated as bbcode.
	 *
	 * @param $parseContent  true iff the content should be parsed
	 */
	public function setParseContent($parseContent)
	{
		$this->parseContent = $parseContent;
		return $this;
	}

	/**
	 * Sets the nest limit for this code definition.
	 *
	 * @param $nestLimit a positive integer, or -1 if there is no limit.
	 * @throws InvalidArgumentException  if the nest limit is invalid
	 */
	public function setNestLimit($limit)
	{
		if(!is_int($limit) || ($limit <= 0 && -1 != $limit)) {
			throw new \InvalidArgumentException("A nest limit must be a positive integer " .
												"or -1.");
		}
		$this->nestLimit = $limit;
		return $this;
	}

	/**
	 * Sets the InputValidator that option arguments should be validated with.
	 *
	 * @param $validator  the InputValidator instance to use
	 */
	public function setOptionValidator(InputValidator $validator)
	{
		$this->optionValidator = $validator;
		return $this;
	}

	/**
	 * Sets the InputValidator that body ({param}) text should be validated with.
	 *
	 * @param $validator  the InputValidator instance to use
	 */
	public function setBodyValidator(InputValidator $validator)
	{
		$this->bodyValidator = $validator;
		return $this;
	}

	/**
	 * Removes the attached option validator if one is attached.
	 */
	public function removeOptionValidator()
	{
		$this->optionValidator = null;
		return $this;
	}

	/**
	 * Removes the attached body validator if one is attached.
	 */
	public function removeBodyValidator()
	{
		$this->bodyValidator = null;
		return $this;
	}
	
	/**
	 * Builds a CodeDefinition with the current state of the builder.
	 *
	 * @return a new CodeDefinition instance
	 */
	public function build()
	{
		$definition = CodeDefinition::construct($this->tagName,
												$this->replacementText,
												$this->useOption,
												$this->parseContent,
												$this->nestLimit,
												$this->optionValidator,
												$this->bodyValidator);
		return $definition;
	}


}
