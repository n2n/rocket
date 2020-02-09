<?php
namespace rocket\ei\util\gui;

use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\util\entry\EiuObject;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\util\EiuPerimeterException;
use rocket\si\content\impl\basic\CompactExplorerSiComp;
use rocket\si\content\SiPartialContent;

class EiuGui {
	private $eiGui;
	private $eiuGuiFrame;
	private $eiuAnalyst;
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @param EiuFrame $eiuFrame
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiGui $eiGui, ?EiuGuiFrame $eiuGuiFrame, EiuAnalyst $eiuAnalyst) {
		$this->eiGui = $eiGui;
		$this->eiuGuiFrame = $eiuGuiFrame;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
// 	/**
// 	 * @return \rocket\ei\util\frame\EiuFrame
// 	 */
// 	public function getEiuFrame() {
// 		if ($this->eiuFrame !== null) {
// 			return $this->eiuFrame;
// 		}
		
// 		if ($this->eiuAnalyst !== null) {
// 			$this->eiuFrame = $this->eiuAnalyst->getEiuFrame(false);
// 		}
		
// 		if ($this->eiuFrame === null) {
// 			$this->eiuFrame = new EiuFrame($this->eiGuiFrame->getEiFrame(), $this->eiuAnalyst);
// 		}
		
// 		return $this->eiuFrame;
// 	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	public function getEiGui() {
		return $this->eiGui;
	}
	
	/**
	 * @return bool
	 */
	public function isSingle() {
		return 1 == count($this->eiGuiFrame->getEiEntryGuis());
	}
	
	function createSiDeclaration() {
		return $this->eiGui->createSiDeclaration();
	}
	
	/**
	 *
	 * @param bool $required
	 * @return EiuEntryGui|null
	 */
	public function entryGui(bool $required = true) {
		$eiEntryGuis = $this->eiGui->getEiEntryGuis();
		
		if (count($eiEntryGuis) == 1) {
			return new EiuEntryGui(current($eiEntryGuis), $this, $this->eiuAnalyst);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No single EiuEntryGui is available.');
	}
	
	public function entryGuis() {
		$eiuEntryGuis = array();
		
		foreach ($this->eiGuiFrame->getEiEntryGuis() as $eiEntryGui) {
			$eiuEntryGuis[] = new EiuEntryGui($eiEntryGui, $this, $this->eiuAnalyst);
		}
		
		return $eiuEntryGuis;
	}
	
	/**
	 *
	 * @param mixed $eiEntryArg
	 * @param bool $makeEditable
	 * @param int $treeLevel
	 * @return EiuEntryGui
	 */
	public function appendNewEntryGui($eiEntryArg, int $treeLevel = null) {
		$eiEntry = null;
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiEntryArg, 'eiEntryArg', $this->eiuFrame->getContextEiType(), true,
				$eiEntry);
		
		if ($eiEntry === null) {
			$eiEntry = (new EiuEntry(null, new EiuObject($eiObject, $this->eiuAnalyst),
					null, $this->eiuAnalyst))->getEiEntry(true);
		}
		
		return new EiuEntryGui($this->eiuAnalyst->getEiGuiFrame(true)
				->createEiEntryGui($eiEntry, $treeLevel, true), $this, $this->eiuAnalyst);
	}
	
	function createCompactExplorerSiComp(int $pageSize = 30) {
		$eiFrame = $this->eiuAnalyst->getEiFrame(true);
		$siDeclaration = $this->eiGui->createSiDeclaration($eiFrame);
		
		$this->composeEiuGuiFrameForList($pageSize);
		$siComp = new CompactExplorerSiComp($this->eiu->frame()->getApiUrl(), $pageSize, $siDeclaration,
				new SiPartialContent($eiFrame->countEntries(), $eiuGuiFrameLayout->getEiGui()->createSiEntries($eiFrame)));
		
		new CompactExplorerSiComp($eiFrame->getApiUrl(), $pageSize);
	}
	
	private function composeEiuGuiFrameForList( $limit) {
		$eiType = $this->eiuFrame->getEiFrame()->getContextEiEngine()->getEiMask()->getEiType();
		
		$criteria = $this->eiuFrame->getEiFrame()->createCriteria(NestedSetUtils::NODE_ALIAS, false);
		$criteria->select(NestedSetUtils::NODE_ALIAS)->limit($limit);
		
		if (null !== ($nestedSetStrategy = $eiType->getNestedSetStrategy())) {
			$this->treeLookup($eiGui, $criteria, $nestedSetStrategy);
		} else {
			$this->simpleLookup($eiGui, $criteria);
		}
	}
	
	private function simpleLookup(EiGui $eiGui, Criteria $criteria) {
		$eiGuiFrame = $eiGui->getEiGuiFrame();
		$eiFrame = $this->eiuFrame->getEiFrame();
		$eiFrameUtil = new EiFrameUtil($eiFrame);
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$eiObject = new LiveEiObject($eiFrameUtil->createEiEntityObj($entityObj));
			$eiGui->addEiEntryGui($eiGuiFrame->createEiEntryGui($eiFrame, $eiFrame->createEiEntry($eiObject)));
		}
	}
	
	private function treeLookup(EiGui $eiGui, Criteria $criteria, NestedSetStrategy $nestedSetStrategy) {
		$nestedSetUtils = new NestedSetUtils($this->eiuFrame->em(), $this->eiuFrame->getContextEiType()->getEntityModel()->getClass(), $nestedSetStrategy);
		
		$eiGuiFrame = $eiGui->getEiGuiFrame();
		$eiFrame = $this->eiuFrame->getEiFrame();
		$eiFrameUtil = new EiFrameUtil($eiFrame);
		foreach ($nestedSetUtils->fetch(null, false, $criteria) as $nestedSetItem) {
			$eiObject = new LiveEiObject($eiFrameUtil->createEiEntityObj($nestedSetItem->getEntityObj()));
			$eiGui->addEiEntryGui($eiGuiFrame->createEiEntryGui($eiFrame, $eiFrame->createEiEntry($eiObject), $nestedSetItem->getLevel()));
		}
	}
}