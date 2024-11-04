<?php

namespace rocket\op\ei\manage\gui\factory;

use rocket\ui\gui\ViewMode;
use rocket\ui\gui\control\GuiControlMap;
use rocket\ui\gui\control\GuiControlKey;
use rocket\ui\gui\impl\BulkyGui;
use rocket\ui\si\meta\SiDeclaration;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\gui\EiGuiMaskFactory;
use rocket\ui\gui\GuiMask;
use n2n\util\type\ArgUtils;
use rocket\impl\ei\manage\gui\CompactExplorerGui;

class EiGuiFactory {

	function __construct(private readonly EiFrame $eiFrame) {

	}

	function createCompactExplorerGui(): CompactExplorerGui {

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
}