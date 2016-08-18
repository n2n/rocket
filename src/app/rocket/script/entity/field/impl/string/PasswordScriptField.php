<?php
namespace rocket\script\entity\field\impl\string;

use n2n\dispatch\option\impl\IntegerOption;
use n2n\util\Attributes;
use n2n\ui\html\HtmlView;
use n2n\persistence\orm\property\EntityProperty;
use n2n\util\crypt\hash\algorithm\BlowfishAlgorithm;
use n2n\util\crypt\hash\HasherFactory;
use n2n\dispatch\option\impl\EnumOption;
use n2n\persistence\orm\property\DefaultProperty;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use rocket\script\entity\field\impl\TranslatableScriptFieldAdapter;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\ManageInfo;
use n2n\dispatch\option\impl\SecretStringOption;
use n2n\util\crypt\hash\algorithm\Sha256Algorithm;

class PasswordScriptField extends TranslatableScriptFieldAdapter {
	const OPTION_ALGORITHM_KEY = 'algorithm';
	const ALGORITHM_SHA1 = 'sha1';
	const ALGORITHM_MD5 = 'md5';
	const ALGORITHM_BLOWFISH = 'blowfish';
	const ALGORITHM_SHA_256 = 'sha-256';
	
	public function isRequired(ScriptSelectionMapping $scriptSelectionMapping, 
			ManageInfo $manageInfo) {
		return false;
	}
	
	public function getTypeName() {
		return 'Password';
	}
	
	public function getMaxlength() {
		return $this->getAttributes()->get(AlphanumericScriptField::OPTION_MAXLENGTH_KEY);
	}
	
	public function setMaxlength($maxlength) {
		$this->getAttributes()->set(AlphanumericScriptField::OPTION_MAXLENGTH_KEY, $maxlength);
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DefaultProperty;
	}
	
	public function isDisplayInDetailViewEnabled() {
		return false;
	}
	
	public function isDisplayInListViewEnabled() {
		return false;
	}
	
	public function createOptionCollection() {
		$optionForm = new OptionCollectionImpl();
		$algorithms = self::getAlgorithms();
		$optionForm->addOption(self::OPTION_ALGORITHM_KEY, 
				new EnumOption('Algortithm', array_combine($algorithms, $algorithms), self::ALGORITHM_BLOWFISH, true));
		$optionForm->addOption(AlphanumericScriptField::OPTION_MAXLENGTH_KEY, 
				new IntegerOption('Maxlength', null, false, 0));
		$this->displayInListViewDefault = false;
		$this->displayInDetailViewDefault = false;
		$this->applyDisplayOptions($optionForm, false, false, true, true, false);
		$this->applyDraftOptions($optionForm);
		$this->applyEditOptions($optionForm, true, false, false);
		$this->applyTranslationOptions($optionForm);
		return $optionForm;
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, 
			HtmlView $view, ManageInfo $manageInfo)  {
		return null;
	}
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return new SecretStringOption($this->getLabel(), null,
				$scriptSelectionMapping->getScriptSelection()->isNew(), $this->getMaxlength(), 
				array('placeholder' => $this->getLabel()));
	}
	
	public function optionAttributeValueToPropertyValue(Attributes $attributes, 
			ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$optionValue = $attributes->get($this->getId());
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		if (mb_strlen($optionValue) === 0 && !$scriptSelection->isNew()) {
			return;
		}
		$propertyValue = null;
		switch ($this->getAttributes()->get(self::OPTION_ALGORITHM_KEY)) {
			case (self::ALGORITHM_BLOWFISH):
				$propertyValue = HasherFactory::createWithDefaultSalt(new BlowfishAlgorithm())->encrypt($optionValue);
				break;
			case (self::ALGORITHM_SHA_256):
				$propertyValue = HasherFactory::createWithDefaultSalt(new Sha256Algorithm())->encrypt($optionValue);
				break;
			case (self::ALGORITHM_MD5):
				$propertyValue = md5($optionValue);
				break;
			case (self::ALGORITHM_SHA1):
				$propertyValue = sha1($optionValue);
				break;
		}
		$scriptSelectionMapping->setValue($this->getId(), $propertyValue);
	}
	
	public static function getAlgorithms() {
		return array(self::ALGORITHM_BLOWFISH, self::ALGORITHM_SHA1, self::ALGORITHM_MD5, self::ALGORITHM_SHA_256);
	}
} 