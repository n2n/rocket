<?php
namespace rocket\script\entity\field\impl\file\command\model;

use n2n\io\fs\img\ImageDimension;
use n2n\dispatch\val\ValEnum;
use n2n\io\fs\img\ThumbFileManager;
use n2n\dispatch\val\ValNumeric;
use n2n\dispatch\map\BindingConstraints;
use rocket\script\entity\field\impl\file\FileScriptField;
use n2n\dispatch\DispatchAnnotations;
use n2n\io\fs\img\ImageFile;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\Dispatchable;

class ThumbModel implements Dispatchable{
	private static function _annotations(AnnotationSet $as) {
		$as->m('save', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $thumbFileManager;
	private $imageFile;
	private $dimensions;
	private $fileExtension;
	
	public $testHtml;
	public $dimensionId;
	public $x;
	public $y;
	public $width;
	public $height;
	public $keepAspectRatio = true;
	
	public function __construct(ImageFile $imageFile, FileScriptField $fileScriptField) {
		$this->thumbFileManager = $imageFile->getFileManager();
		if (!($this->thumbFileManager instanceof ThumbFileManager)) {
			throw new \InvalidArgumentException(get_class($this->thumbFileManager) . ' is no ImageThumbManager');
		}
		$this->imageFile = $imageFile;
		
		$this->x = null;
		$this->y = null;
		$this->width = null;
		$this->height = null;
				
		$this->dimensions = $fileScriptField->getThumbDimensions();
		$this->fileExtension = $fileScriptField->getAllowedExtensions();
	}
	
	public function getImageFile() {
		return $this->imageFile;
	}
	
	public function getDimensions() {
		return $this->dimensions;
	}
	
	public function getDimensionOptions() {
		$options = array();
		foreach ($this->dimensions as $dimension) {
			$options[$dimension->__toString()] = $dimension->getWidth() . ' x ' . $dimension->getHeight();
		}
		return $options;
	}
	
	private function _validation(BindingConstraints $bc) {
		$bc->val(array('x', 'y', 'width', 'height'), new ValNumeric(true, null, 0));
		$bc->val('dimensionId', new ValEnum(array_keys($this->getDimensionOptions())));
	}
	
	public function save() {
		$dimension = ImageDimension::createFromString($this->dimensionId);
		
		$imageResource = $this->imageFile->getFileWrapper()->createResource();

		$imageResource->crop($this->x, $this->y, $this->width, $this->height);
		$imageResource->resize($dimension->getWidth(), $dimension->getHeight(), $dimension->getCrop()/* || !$this->keepAspectRatio*/);
				
		$fileWrapper = ImageFile::createFileWrapper($this->imageFile->getMimeType(), 
				$this->thumbFileManager->createThumbFilePath($this->imageFile->getFile(), $dimension));
		$fileWrapper->saveResource($imageResource);
		$imageResource->destroy();
	}
}

// if (isset($_POST['formatbreite']) && isset($_POST['formathoehe']) && isset($_POST['xwert'])
// 		&& isset($_POST['ywert']) && isset($_POST['ausschnittbreite']) && isset($_POST['ausschnitthoehe'])
// 		&& isset($_POST['anschneiden'])
// ) {
// 	$dimension = $this->getDimension($imageScriptField, $_POST['formatbreite'], $_POST['formathoehe']);
// 	if (!$dimension) {
// 		$mc->addError($text->get('err_image_resize_invalid_format',
// 				array('width' => $_POST['formatbreite'], 'height' => $_POST['formathoehe'])));
// 	} else {
// 		// @todo: @_POST['anschneiden'] has to be inverted, in order to give correct result!
// 		$resizeModel->updateThumb($dimension, $_POST['xwert'], $_POST['ywert'], $_POST['ausschnittbreite'],
// 				$_POST['ausschnitthoehe'], !(boolean) $_POST['anschneiden']);
	
// 		$mc->addInfo($text->get('image_resize_thumb_created',
// 				array('width' => $dimension->getWidth(), 'height' => $dimension->getHeight())));

// 	}
// }

// public function updateThumb(NN6FileImageDimension $dimension, $x, $y, $width, $height, $crop) {
// 	$fileManager = $this->image->getFileManager();

// 	$endWidth = $dimension->getWidth();
// 	$endHeight = $dimension->getHeight();
// 	$crop = ($dimension->getCrop() || $crop);
// 	$imageResource = $this->image->createResource();
// 	$imageResource->crop($x, $y, $width, $height);
// 	$imageResource->resize($endWidth, $endHeight, $crop);

// 	$fileManager->setImageResFromResource($this->image, $dimension, $imageResource);
// 	$imageResource->destroy();
// }