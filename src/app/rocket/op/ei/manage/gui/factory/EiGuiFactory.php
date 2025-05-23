<?php

namespace rocket\op\ei\manage\gui\factory;

use rocket\ui\gui\ViewMode;
use rocket\ui\gui\impl\BulkyGui;
use rocket\ui\si\meta\SiDeclaration;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\gui\EiGuiMaskFactory;
use rocket\ui\gui\GuiMask;
use n2n\util\type\ArgUtils;
use rocket\ui\gui\impl\CompactExplorerGui;
use rocket\op\ei\manage\frame\EiObjectSelector;
use rocket\ui\gui\GuiValueBoundary;
use rocket\ui\si\content\SiPartialContent;
use rocket\ui\gui\impl\CompactGui;
use rocket\op\ei\manage\gui\EiSiMaskId;

class EiGuiFactory {

	function __construct(private readonly EiFrame $eiFrame) {

	}

	function createCompactExplorerGui(int $pageSize = 30, bool $populateFirstPage = true): CompactExplorerGui {
		$guiMasks = [];

		$eiSiMaskId = new EiSiMaskId($this->eiFrame->getContextEiEngine()->getEiMask()->getEiTypePath(), ViewMode::COMPACT_READ);

		if (!$populateFirstPage) {
			return new CompactExplorerGui($this->eiFrame->createSiFrame(), $pageSize, $eiSiMaskId,
					new SiDeclaration([]), null);
		}

		$eiObjectSelector = new EiObjectSelector($this->eiFrame);
		$records = $eiObjectSelector->lookupEiEntries(0, $pageSize, null);

		$factory = new EiGuiValueBoundaryFactory($this->eiFrame);
		$guiValueBoundaries = [];
		foreach ($records as $record) {
			$guiValueBoundaries[] = $factory->create($record->treeLevel, [$record->eiEntry], ViewMode::COMPACT_READ);
		}

		$eiGuiMaskFactory = new EiGuiMaskFactory($this->eiFrame);
		$guiMasks = $eiGuiMaskFactory->createGuiMasksOfEiEntries(array_map(fn ($r) => $r->eiEntry, $records),
				ViewMode::COMPACT_READ);

		if (empty($guiMasks)) {
			$guiMasks[] = $this->eiFrame->getContextEiEngine()->getEiGuiDefinition(ViewMode::COMPACT_READ)
					->createGuiMask($this->eiFrame);
		}

		$count = $eiObjectSelector->count();

		return new CompactExplorerGui($this->eiFrame->createSiFrame(), $pageSize, $eiSiMaskId,
				new SiDeclaration(array_map(fn (GuiMask $m) => $m->getSiMask(), $guiMasks)),
				new SiPartialContent($count,
						array_map(fn (GuiValueBoundary $b) => $b->getSiValueBoundary(), $guiValueBoundaries)));
	}

	/**
	 * @param EiEntry[] $eiEntries
	 * @param bool $readOnly
	 * @return BulkyGui
	 */
	function createBulkyGui(array $eiEntries, bool $readOnly): BulkyGui {
		ArgUtils::assertTrue(!empty($eiEntries), 'EiEntries array empty');

		$new = null;
		foreach ($eiEntries as $eiEntry) {
			if ($new === null) {
				$new = $eiEntry->isNew();
				continue;
			}

			ArgUtils::assertTrue($new === $eiEntry->isNew(),
					'Some passed EiEntries are new others not.');
		}

		$viewMode = ViewMode::determine(true, $readOnly, $new);

		$eiGuiValueBoundaryFactory = new EiGuiValueBoundaryFactory($this->eiFrame);
		$guiValueBoundary = $eiGuiValueBoundaryFactory->create(null, $eiEntries, $viewMode);

		$eiGuiMaskFactory = new EiGuiMaskFactory($this->eiFrame);
		$guiMasks = $eiGuiMaskFactory->createGuiMasksOfEiEntries($eiEntries, $viewMode);

		return new BulkyGui($this->eiFrame->createSiFrame(),
				new SiDeclaration(array_map(fn (GuiMask $m) => $m->getSiMask(), $guiMasks)),
				$guiValueBoundary);
	}

	function createCompactGui(array $eiEntries, bool $readOnly): CompactGui {
		ArgUtils::assertTrue(!empty($eiEntries), 'EiEntries array empty');

		$new = null;
		foreach ($eiEntries as $eiEntry) {
			if ($new === null) {
				$new = $eiEntry->isNew();
				continue;
			}

			ArgUtils::assertTrue($new === $eiEntry->isNew(),
					'Some passed EiEntries are new others not.');
		}

		$viewMode = ViewMode::determine(false, $readOnly, $new);

		$eiGuiValueBoundaryFactory = new EiGuiValueBoundaryFactory($this->eiFrame);
		$guiValueBoundary = $eiGuiValueBoundaryFactory->create(null, $eiEntries, $viewMode);

		$eiGuiMaskFactory = new EiGuiMaskFactory($this->eiFrame);
		$guiMasks = $eiGuiMaskFactory->createGuiMasksOfEiEntries($eiEntries, $viewMode);

		return new CompactGui($this->eiFrame->createSiFrame(),
				new SiDeclaration(array_map(fn (GuiMask $m) => $m->getSiMask(), $guiMasks)),
				$guiValueBoundary);
	}
}