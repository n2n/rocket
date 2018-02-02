<?php

namespace rocket\spec\ei\manage;

use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\impl\web\ui\view\html\HtmlUtils;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\dispatch\mag\MagCollection;
use n2n\web\dispatch\mag\UiOutfitter;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlSnippet;

class RocketUiOutfitter implements UiOutfitter {

	public function __construct() {
	}

	/**
	 * @param string $nature
	 * @return array
	 */
	public function createAttrs(int $nature): array {
		$attrs = array();
		if ($nature & self::NATURE_MAIN_CONTROL) {
			return ($nature & self::NATURE_CHECK) ? array('class' => 'form-check-input') : array('class' => 'form-control');
		}

		if ($nature & self::NATURE_CHECK_LABEL) {
			return array('class' => 'form-check-label');
		}

		if ($nature & self::NATURE_BTN_PRIMARY) {
			return array('class' => 'btn btn-primary mt-2');
		}

		if ($nature & self::NATURE_BTN_SECONDARY) {
			return array('class' => 'btn btn-secondary');
		}

		if ($nature & self::NATURE_MASSIVE_ARRAY_ITEM) {
			return array('class' => 'col-md-12', 'style' => 'background: green');
		}

		return $attrs;
	}

	/**
	 * @param int $elemNature
	 * @param array|null $attrs
	 * @param null $contents
	 * @return HtmlElement
	 */
	public function createElement(int $elemNature, array $attrs = null, $contents = null): UiComponent {
		if ($elemNature & self::EL_NATRUE_CONTROL_ADDON_SUFFIX_WRAPPER) {
			return new HtmlElement('div', HtmlUtils::mergeAttrs(array('class' => 'input-group'), $attrs), $contents);
		}

		if ($elemNature & self::EL_NATURE_CONTROL_ADDON_WRAPPER) {
			return new HtmlElement('span', HtmlUtils::mergeAttrs(array('class' => 'input-group-addon'), $attrs), $contents);
		}

		if ($elemNature & self::NATURE_MASSIVE_ARRAY_ITEM && $elemNature & self::NATURE_MASSIVE_ARRAY_ITEM_CONTROL) {
			$container = new HtmlElement('div', array('class' => 'row'), null);

			$container->appendLn(new HtmlElement('div', array('class' => 'col-auto'), $contents));
			$container->appendLn(new HtmlElement('div', array('class' => 'col-auto mag-collection-control-wrapper'), ''));

			return $container;
		}

		if ($elemNature & self::EL_NATURE_CONTROL_ADD) {
			return new HtmlElement('button', HtmlUtils::mergeAttrs(
				$this->createAttrs(UiOutfitter::NATURE_BTN_SECONDARY), $attrs),
				new HtmlElement('i', array('class' => UiOutfitter::ICON_NATURE_ADD), $contents));
		}

		if ($elemNature & self::EL_NATURE_CONTROL_REMOVE) {
			return new HtmlElement('button', HtmlUtils::mergeAttrs(
				$this->createAttrs(UiOutfitter::NATURE_BTN_SECONDARY), $attrs),
				new HtmlElement('i', array('class' => UiOutfitter::ICON_NATURE_REMOVE), $contents));
		}


		if ($elemNature & self::EL_NATURE_ARRAY_ITEM_CONTROL) {
			$container = new HtmlElement('div', array('class' => 'row'), '');

			$container->appendLn(new HtmlElement('div', array('class' => 'col-auto'), $contents));
			$container->appendLn(new HtmlElement('div',
				array('class' => 'col-auto ' . MagCollection::CONTROL_WRAPPER_CLASS),
				$this->createElement(UiOutfitter::EL_NATURE_CONTROL_REMOVE, array('class' => MagCollection::CONTROL_REMOVE_CLASS), '')));

			return $container;
		}

		return new HtmlSnippet($contents);
	}

	public function createMagDispatchableView(PropertyPath $propertyPath = null, HtmlView $contextView): UiComponent {
		$showLabel = true;
		if ($propertyPath === null) {
			//$showLabel = false;
		}

		return $contextView->getImport('\rocket\spec\ei\manage\gui\view\magForm.html',
			array('propertyPath' => $propertyPath, 'uo' => $this, 'showLabel' => $showLabel));
	}
}