<?php
namespace rocket\script\entity\command\control;

use n2n\ui\html\InputField;

use n2n\ui\Raw;

use n2n\ui\html\HtmlElement;


class ControlButton {
	const TYPE_DEFAULT = null;
	const TYPE_SUCCESS = 'success';
	const TYPE_DANGER = 'danger';
	const TYPE_INFO = 'info';
	const TYPE_WARNING = 'warning';
	
	private $url;
	private $name;
	private $tooltip;
	private $important;
	private $type;
	private $iconType;
	
	public function __construct($url, $name, $tooltip, $important, $type = self::TYPE_DEFAULT, $iconType = null) {
		$this->url = $url;
		$this->name = $name;
		$this->tooltip = $tooltip;
		$this->important = $important;
		$this->type = $type;
		$this->iconType = $iconType;
	}
	
	public function isImportant() {
		return $this->important;
	}
	
	public function getName() {
		return $this->name;
	}

	public function getType() {
		return $this->type;
	}
	
	public function getTooltip() {
		return $this->tooltip;
	}
	
	public function setConfirmMessage($confirmMessage) {
		$this->confirmMessage = $confirmMessage;
	}
	
	public function getConfirmMessage() {
		return $this->confirmMessage;
	}
	
	public function setConfirmOkButtonLabel($confirmOkButtonLabel) {
		$this->confirmOkButtonLabel = $confirmOkButtonLabel;
	}
	
	public function getConfirmOkButtonLabel() {
		return $this->confirmOkButtonLabel;
	}
	
	public function setConfirmCancelButtonLabel($confirmCancelButtonLabel) {
		$this->confirmCancelButtonLabel = $confirmCancelButtonLabel;
	}
	
	public function getConfirmCancelButtonLabel($confirmCancelButtonLabel) {
		return $this->confirmCancelButtonLabel;
	}
	
	private function applyAttrs(array $attrs) {
		if ($this->tooltip !== null) {
			$attrs['title'] = $this->tooltip;
		}
		if ($this->type !== null) {
			$attrs['class'] = 'rocket-control-' . $this->type;
		} else {
			$attrs['class'] = 'rocket-control';
		}
		if ($this->important) {
			$attrs['class'] .= ' rocket-important';	
		}
		if (isset($this->confirmMessage)) {
			$attrs['data-rocket-confirm-msg'] = $this->confirmMessage;
		}
		if (isset($this->confirmOkButtonLabel)) {
			$attrs['data-rocket-confirm-ok-label'] = $this->confirmOkButtonLabel;
		}
		if (isset($this->confirmCancelButtonLabel)) {
			$attrs['data-rocket-confirm-cancel-label'] = $this->confirmCancelButtonLabel;
		}
		
		return $attrs;
	}
	
	public function toButton($iconOnly) {
		$label = new Raw(new HtmlElement('i', array('class' => $this->iconType), '') . ' '
				. new HtmlElement('span', null, $this->name));
		
		return new HtmlElement('a', $this->applyAttrs(array('href' => $this->url)), $label);
	}
	
	public function toSubmitButton(InputField $inputField) {
		$attrs = $inputField->getAttrs();
		$uiButton = new HtmlElement('button', $this->applyAttrs($attrs));
		$uiButton->appendContent(new HtmlElement('i', array('class' => $this->iconType), ''));
// 		$uiButton->appendNl();
		$uiButton->appendContent(new HtmlElement('span', null, $this->name));
		return $uiButton;
	}
}