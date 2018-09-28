<?php
namespace rocket\ei\util\gui;

use n2n\util\col\ArrayUtils;
use n2n\util\ex\IllegalStateException;
use n2n\web\dispatch\map\PropertyPath;
use rocket\ei\manage\gui\Displayable;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\ei\manage\gui\GuiFieldAssembly;
use rocket\ei\manage\gui\GuiIdPath;
use rocket\ei\manage\gui\ui\DisplayItem;
use rocket\ei\manage\entry\EiFieldValidationResult;

class EiuHtmlBuilderState {
	private $stack = array();
	
	/**
	 * @return boolean
	 */
	public function containsEntry() {
		foreach ($this->stack as $info) {
			if ($info['type'] == 'entry') return true;
		}
		
		return false;
	}
	
	/**
	 * @param string $tagName
	 * @param EiEntryGui $eiEntryGui
	 */
	public function pushEntry(string $tagName, EiEntryGui $eiEntryGui) {
		$this->stack[] = array(
				'type' => 'entry',
				'eiEntryGui' => $eiEntryGui,
				'tagName' => $tagName);
	}
	
	/**
	 *
	 * @param string $tagName
	 * @throws IllegalStateException
	 * @return array
	 */
	public function peakEntry() {
		for ($i = count($this->stack) - 1; $i >= 0; $i--) {
			if ($this->stack[$i]['type'] == 'entry') {
				return $this->stack[$i];
			}
		}
		
		throw new IllegalStateException('No entry open.');
	}
	
	/**
	 *
	 * @throws IllegalStateException
	 * @return array
	 */
	public function popEntry() {
		$info = ArrayUtils::end($this->stack);
		
		if ($info === null) {
			throw new IllegalStateException('No entry open.');
		}
		
		if ($info['type'] != 'entry') {
			throw new IllegalStateException('Field open.');
		}
		
		array_pop($this->stack);
		
		return $info;
	}
	
	/**
	 * @param string $tagName
	 * @param EiFieldValidationResult $fieldErrorInfo
	 * @param Displayable $guiFieldAssembly
	 * @param PropertyPath $propertyPath
	 */
	public function pushField(string $tagName, GuiIdPath $guiIdPath, EiFieldValidationResult $fieldErrorInfo, 
			GuiFieldAssembly $guiFieldAssembly = null, PropertyPath $propertyPath = null, DisplayItem $displayItem = null) {
		$this->stack[] = array('type' => 'field', 'guiIdPath' => $guiIdPath, 'tagName' => $tagName, 'guiFieldAssembly' => $guiFieldAssembly,
				'fieldErrorInfo' => $fieldErrorInfo, 'propertyPath' => $propertyPath, 'displayItem' => $displayItem);
	}
	
	/**
	 * @param bool $pop
	 * @throws IllegalStateException
	 * @return array
	 */
	public function peakField(bool $pop) {
		$info = ArrayUtils::end($this->stack) ;
		
		if ($info === null || $info['type'] != 'field') {
			throw new IllegalStateException('No field open.');
		}
		
		if ($pop) {
			return array_pop($this->stack);
		} else {
			return end($this->stack);
		}
	}
	
	
	/**
	 * @param string $tagName
	 */
	public function pushGroup(string $tagName) {
		$this->stack[] = array('type' => 'group', 'tagName' => $tagName);
	}
	
	/**
	 *
	 * @param bool $pop
	 * @throws IllegalStateException
	 * @return array
	 */
	public function peakGroup(bool $pop) {
		$info = ArrayUtils::end($this->stack);
		
		if ($info === null || $info['type'] != 'group') {
			throw new IllegalStateException('No group open.');
		}
		
		if ($pop) {
			return array_pop($this->stack);
		} else {
			return end($this->stack);
		}
	}
}