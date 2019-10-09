import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiPage } from '../../model/si-page';
import { ListZoneContentComponent } from './list-zone-content.component';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { SiUiStructureModelFactory } from '../../model/si-ui-structure-model-factory';

export class StructurePage {
	public uiStructuresMap: Map<string, Array<UiStructure>>|null = null;

	constructor(readonly siPage: SiPage) {

	}

	get loaded(): boolean {
		return !!this.siPage.entries;
	}

	clear() {
		for (const [, uiStructures] of this.uiStructuresMap) {
			for (const uiStructure of uiStructures) {
				uiStructure.dispose();
			}
		}
		this.uiStructuresMap = null;
	}

	fieldUiStructuresOf(id: string): UiStructure[] {
		return this.uiStructuresMap.get(id);
	}
}

export class StructurePageManager {
	private pagesMap = new Map<number, StructurePage>();

	constructor(private comp: ListZoneContentComponent) {

	}

	map(siPages: SiPage[]) {
		const structurePages = new Array<StructurePage>();

		for (const siPage of siPages) {
			let structurePage: StructurePage;
			if (this.pagesMap.has(siPage.num)) {
				structurePage = this.pagesMap.get(siPage.num);
			} else {
				structurePage = this.createPage(siPage);
			}

			this.val(structurePage);
			structurePages.push(structurePage);
		}

		return structurePages;
	}

	clear() {
		for (const [, structurePage] of this.pagesMap) {
			structurePage.clear();
		}
		this.pagesMap.clear();
	}

	private createPage(siPage: SiPage): StructurePage {
		const sp = new StructurePage(siPage);
		this.pagesMap.set(siPage.num, sp);
		return sp;
	}

	private val(structurePage: StructurePage) {
		if (structurePage.uiStructuresMap || !this.comp.siPageCollection.declaration
				|| !structurePage.siPage.entries) {
			return;
		}

		const structures = new Map<string, Array<UiStructure>>();
		for (const siEntry of structurePage.siPage.entries) {
			structures.set(siEntry.identifier.id, this.createFieldUiStructures(siEntry));
		}
		structurePage.uiStructuresMap = structures;
	}

	private createFieldUiStructures(siEntry: SiEntry): UiStructure[] {
		const uiStructures = new Array<UiStructure>();

		for (const siProp of this.comp.getSiProps()) {
			const uiStructure = this.comp.uiStructure.createChild();
			uiStructure.model = SiUiStructureModelFactory.createCompactField(siEntry.selectedEntryBuildup.getFieldById(siProp.id));
			uiStructures.push(uiStructure);
		}

		return uiStructures;
	}
}


