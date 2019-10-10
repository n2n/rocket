import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiPage } from '../../model/si-page';
import { ListZoneContentComponent } from './list-zone-content.component';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { SiUiStructureModelFactory } from '../../model/si-ui-structure-model-factory';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

export class StructurePage {
	public fieldUiStructuresMap: Map<string, Array<UiStructure>>|null = null;
	public controlUiStructuresMap: Map<string, Array<UiContent>>|null = null;

	constructor(readonly siPage: SiPage) {
	}

	get loaded(): boolean {
		return !!this.siPage.entries;
	}

	clear() {
		for (const [, uiStructures] of this.fieldUiStructuresMap) {
			for (const uiStructure of uiStructures) {
				uiStructure.dispose();
			}
		}
		this.fieldUiStructuresMap = null;
	}

	fieldUiStructuresOf(id: string): UiStructure[] {
		return this.fieldUiStructuresMap.get(id);
	}

	controlUiContentsOf(id: string): UiContent[] {
		return this.controlUiStructuresMap.get(id);
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
		if (structurePage.fieldUiStructuresMap || !this.comp.siPageCollection.declaration
				|| !structurePage.siPage.entries) {
			return;
		}

		const fieldUiStructures = new Map<string, Array<UiStructure>>();
		const controlUiContentMap = new Map<string, Array<UiContent>>();
		for (const siEntry of structurePage.siPage.entries) {
			fieldUiStructures.set(siEntry.identifier.id, this.createFieldUiStructures(siEntry));
			controlUiContentMap.set(siEntry.identifier.id,
				siEntry.selectedEntryBuildup.controls.map(siControl => siControl.createUiContent()));
		}
		structurePage.fieldUiStructuresMap = fieldUiStructures;
		structurePage.controlUiStructuresMap  = controlUiContentMap;
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


