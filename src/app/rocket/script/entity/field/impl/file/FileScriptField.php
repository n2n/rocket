<?php
namespace rocket\script\entity\field\impl\file;

use n2n\io\fs\img\ImageManagable;
use n2n\ui\html\Link;
use rocket\script\entity\command\control\IconType;
use n2n\ui\html\HtmlElement;
use rocket\script\core\SetupProcess;
use n2n\dispatch\option\impl\StringArrayOption;
use n2n\io\fs\img\ImageDimension;
use n2n\dispatch\option\impl\FileOption;
use n2n\persistence\orm\property\FileProperty;
use n2n\l10n\Locale;
use n2n\persistence\orm\Entity;
use n2n\ui\html\HtmlView;
use n2n\persistence\orm\property\EntityProperty;
use n2n\dispatch\option\impl\BooleanOption;
use rocket\script\entity\field\HighlightableScriptField;
use rocket\script\entity\field\impl\TranslatableScriptFieldAdapter;
use rocket\script\entity\field\impl\file\command\ThumbScriptCommand;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\ManageInfo;
use n2n\io\fs\HttpAccessible;

class FileScriptField extends TranslatableScriptFieldAdapter implements HighlightableScriptField {
	const OPTION_CHECK_IMAGE_RESOURCE_MEMORY_KEY = 'checkImageResourceMemory';
	const OPTION_CHECK_IMAGE_RESOURCE_MEMORY_DEFAULT = true;
	const OPTION_ALLOWED_EXTENSIONS_KEY = 'allowedExtensions';
	const OPTION_THUMB_DIMENSIONS_KEY = 'thumbDimensions';
	
	private $thumbScriptCommand;
	
	public function getTypeName() {
		return 'File';
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof FileProperty; 
	}
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		if (!$this->isThumbCreationEnabled()) {
			return;
		}
		
		$this->thumbScriptCommand = new ThumbScriptCommand();
		$this->thumbScriptCommand->setFileScriptField($this);
		$this->getEntityScript()->getTopEntityScript()->getCommandCollection()->add($this->thumbScriptCommand);
	}
	
	public function getAllowedExtensions() {
		return (array) $this->getAttributes()->get(self::OPTION_ALLOWED_EXTENSIONS_KEY);
	}
	
	public function setAllowedExtensions(array $allowedExtensions = null) {
		$this->getAttributes()->set(self::OPTION_ALLOWED_EXTENSIONS_KEY, (array) $allowedExtensions);
	}
	
	public function isThumbCreationEnabled() {
		return (boolean) sizeof($this->getThumbDimensions());
	}
	
	public function isCheckImageResourceMemoryEnabled() {
		return (boolean) $this->getAttributes()->get(self::OPTION_CHECK_IMAGE_RESOURCE_MEMORY_KEY);
	}
	
	public function setCheckImageResourceMemoryEnabled($checkImageResourceMemory) {
		$this->getAttributes()->set(self::OPTION_CHECK_IMAGE_RESOURCE_MEMORY_KEY, (boolean)$checkImageResourceMemory);
	}
	
	public function getThumbDimensions() {
		$thumbDimensionStrs = (array) $this->getAttributes()->get(self::OPTION_THUMB_DIMENSIONS_KEY);
		$dimensions = array();
		foreach ($thumbDimensionStrs as $thumbDimensionStr) {
			try {
				$dimensions[] = ImageDimension::createFromString($thumbDimensionStr);
			} catch (\InvalidArgumentException $e) { }
		}
		
		return $dimensions;
	}
	
	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();
		$optionCollection->addOption(self::OPTION_ALLOWED_EXTENSIONS_KEY, new StringArrayOption('Allowed Extensions', array(), false));
		$optionCollection->addOption(self::OPTION_THUMB_DIMENSIONS_KEY, new StringArrayOption('Thumb Dimensions', array(), false));
		$optionCollection->addOption(self::OPTION_CHECK_IMAGE_RESOURCE_MEMORY_KEY, new BooleanOption('Check Image Resource Memory', 
				self::OPTION_CHECK_IMAGE_RESOURCE_MEMORY_DEFAULT));
		return $optionCollection;
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view, ManageInfo $manageInfo) {
		$html = $view->getHtmlBuilder();
		$file = $scriptSelectionMapping->getValue($this->id);
		$uiComponent = $html->getEsc($file);
		if (isset($file)) {
			
			//@todo add Fancybox-js here, it is necessery to apply dependecies to scripts
			if (ImageManagable::isImageManagable($file) && $file->getFileManager() instanceof HttpAccessible) {
				$view->getHtmlBuilder()->addCss('js/thirdparty/colorbox/colorbox.css', 'screen');
				$view->getHtmlBuilder()->addJs('js/thirdparty/colorbox/jquery.colorbox-min.js');
				$view->getHtmlBuilder()->addJs('js/image-preview.js');
				$uiComponent = new HtmlElement('div', null, new Link($file->getHttpPath(), $html->getImage($file, new ImageDimension(40, 28, true), 
						array('title' => $file->getOriginalName())), array('class' => 'rocket-image-previewable')));
				if ($this->isThumbCreationEnabled()) {
					$uiComponent->appendContent($html->getLinkToController(array($this->thumbScriptCommand->getId(), 
									$scriptSelectionMapping->getScriptSelection()->getId()),
							new HtmlElement('i', array('class' => IconType::ICON_CROP), ''), 
							array('title' => $view->getL10nText('script_field_display_image_resizer'),
									'class' => 'rocket-control rocket-simple-controls'), null, null,
							$manageInfo->getScriptState()->getControllerContext()));
				}
			} else if ($file->getFileManager() instanceof HttpAccessible) {
				$uiComponent = new Link($file->getHttpPath(), $uiComponent, array('target' => '_blank'));
			}
		}
		return $uiComponent;
	}
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$allowedExtensions = $this->getAllowedExtensions();
		return new FileOption($this->getLabel(), (sizeof($allowedExtensions) ? $allowedExtensions : null), 
				$this->isCheckImageResourceMemoryEnabled(), null, 
				$this->isRequired($scriptSelectionMapping, $manageInfo), 
				array('placeholder' => $this->getLabel()));
	}
	
	public function createKnownString(Entity $entity, Locale $locale) {
		return $this->getPropertyAccessProxy()->getValue($entity);
	}
}