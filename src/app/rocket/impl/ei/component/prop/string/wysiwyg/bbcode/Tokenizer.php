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
 * This Tokenizer is used while constructing the parse tree. The tokenizer
 * handles splitting the input into brackets and miscellaneous text. The
 * parser is then built as a FSM ontop of these possible inputs.
 *
 * @author jbowens
 */
class Tokenizer
{

	protected $tokens = array();
	protected $i = -1;

	/**
	 * Constructs a tokenizer from the given string. The string will be tokenized
	 * upon construction.
	 *
	 * @param $str  the string to tokenize
	 */
	public function __construct($str)
	{
		$strStart = 0;
		for ($index = 0; $index < strlen($str); ++$index) {
			if (']' == $str[$index] || '[' == $str[$index]) {
				/* Are there characters in the buffer from a previous string? */
				if ($strStart < $index) {
					array_push($this->tokens, substr($str, $strStart, $index - $strStart));
					$strStart = $index;
				}

				/* Add the [ or ] to the tokens array. */
				array_push($this->tokens, $str[$index]);
				$strStart = $index+1;
			}
		}

		if ($strStart < strlen($str)) {
			/* There are still characters in the buffer. Add them to the tokens. */
			array_push($this->tokens, substr($str, $strStart, strlen($str) - $strStart));
		}
	}

	/**
	 * Returns true iff there is another token in the token stream.
	 */
	public function hasNext()
	{
		return count($this->tokens) > 1 + $this->i;
	}

	/**
	 * Advances the token stream to the next token and returns the new token.
	 */
	public function next()
	{
		if (!$this->hasNext()) {
			return null;
		} else {
			return $this->tokens[++$this->i];
		}
	}

	/**
	 * Retrieves the current token.
	 */
	public function current()
	{
		if ($this->i < 0) {
			return null;
		} else {
			return $this->tokens[$this->i];
		}
	}

	/**
	 * Moves the token stream back a token.
	 */
	public function stepBack()
	{
		if ($this->i > -1) {
			$this->i--;
		}
	}

	/**
	 * Restarts the tokenizer, returning to the beginning of the token stream.
	 */
	public function restart()
	{
		$this->i = -1;
	}

	/**
	 * toString method that returns the entire string from the current index on.
	 */
	public function toString()
	{
		return implode('', array_slice($this->tokens, $this->i + 1));
	}

}
