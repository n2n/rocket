<?php
namespace rocket\op\ei\util\factory;

use rocket\op\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\gui\ClosureGuiField;
use rocket\ui\si\content\SiField;
use rocket\ui\gui\field\GuiField;
use Closure;

class EifGuiField {
	private SiField $siField;
	private ?Closure $messagesClosure = null;
	private ?Closure $readClosure = null;
	private ?Closure $saveClosure = null;
	
	
	/**
	 * @param Eiu $eiu
	 * @param SiField $siField
	 */
	function __construct(Eiu $eiu, SiField $siField) {
//		$this->eiu = $eiu;
		$this->siField = $siField;
	}

	function setMessagesBearer(?Closure $closure): static {
		$this->messagesClosure = $closure;
		return $this;
	}

	function setReader(?Closure $closure): static {
		if ($closure !== null && $this->siField->isReadOnly()) {
			throw new \InvalidArgumentException('Reader disallowed for read only SiField.');
		}

		$this->readClosure = $closure;
		return $this;
	}

	/**
	 * @param Closure|null $closure
	 * @return EifGuiField
	 */
	function setSaver(?Closure $closure): static {
		if ($closure !== null && $this->siField->isReadOnly()) {
			throw new \InvalidArgumentException('Saver disallowed for read only SiField.');
		}
		
		$this->saveClosure = $closure;
		return $this;
	}

	function toGuiField(): GuiField {
		return new ClosureGuiField($this->siField, $this->messagesClosure, $this->readClosure, $this->saveClosure);
	}
}