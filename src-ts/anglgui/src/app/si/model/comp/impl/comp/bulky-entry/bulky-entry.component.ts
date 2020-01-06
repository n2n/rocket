import { Component, OnInit, OnDestroy, DoCheck } from '@angular/core';
import { BulkyEntryModel } from '../bulky-entry-model';
import { SiStructureDeclaration } from 'src/app/si/model/meta/si-structure-declaration';
import { SiUiStructureModelFactory } from '../../model/si-ui-structure-model-factory';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { StructureBranchComponent } from 'src/app/ui/structure/comp/structure-branch/structure-branch.component';
import { SiField } from 'src/app/si/model/content/si-field';
import { SiFieldAdapter } from 'src/app/si/model/content/impl/common/model/si-field-adapter';

@Component({
	selector: 'rocket-bulky-entry',
	templateUrl: './bulky-entry.component.html'
})
export class BulkyEntryComponent implements OnInit, OnDestroy, DoCheck {
	public model: BulkyEntryModel;
	public uiStructure: UiStructure;

	public siEntry: SiEntry|null = null;
	public fieldUiStructures: UiStructure[] = [];

	constructor() { }

	ngOnInit() {
		this.sync();
	}

	ngDoCheck() {
		this.sync();
	}

	ngOnDestroy() {
		this.clear();
	}

	private sync() {
		const siEntry = this.model.getSiEntry();
		if (this.siEntry === siEntry) {
			return;
		}

		this.clear();
		this.siEntry = siEntry;

		if (siEntry === null) {
			return;
		}

		const siTypeDeclaration = this.model.getSiDeclaration().getTypeDeclarationByTypeId(siEntry.selectedTypeId);

		this.fieldUiStructures = this.createStructures(this.uiStructure, siTypeDeclaration.structureDeclarations);
	}

	private clear() {
		if (!this.fieldUiStructures) {
			return;
		}

		let uiStructure: UiStructure|null = null;
		while (uiStructure = this.fieldUiStructures.pop()) {
			uiStructure.dispose();
		}
	}

	private createStructures(parent: UiStructure, uiStructureDeclarations: SiStructureDeclaration[]): UiStructure[] {
		const structures: UiStructure[] = [];
		for (const usd of uiStructureDeclarations) {
			structures.push(this.dingsel(parent, usd));
		}
		return structures;
	}

	private dingsel(parent: UiStructure, ssd: SiStructureDeclaration): UiStructure {
		const uiStructure = parent.createContentChild();
		uiStructure.label = ssd.prop ? ssd.prop.label : ssd.label;
		uiStructure.type = ssd.type;

		if (ssd.prop) {
			uiStructure.label = ssd.prop.label;
			uiStructure.model = this.model.getSiEntry().selectedEntryBuildup.getFieldById(ssd.prop.id)
					.createUiStructureModel();
			return uiStructure;
		}

		uiStructure.label = ssd.label;
		uiStructure.model = new SimpleUiStructureModel(
				new TypeUiContent(StructureBranchComponent, (ref) => {
					ref.instance.uiStructure = uiStructure;
				}));

		this.createStructures(uiStructure, ssd.children);

		return uiStructure;
	}

}

class ToolbarResolver {
	private uiStructuresMap = new Map<string, { siField: SiField, uiStructure: UiStructure } | null>();

	register(propId: string, siField: SiField, uiStructure: UiStructure) {
		this.uiStructuresMap.set(propId, null);

		for (const contextSiField of siField.getContextSiFields()) {
			this.addContextSiField(propId, contextSiField, uiStructure);
		}
	}

	private addContextSiField(propId: string, siField: SiField, uiStructure: UiStructure) {
		if (!this.uiStructuresMap.has(propId)) {
			this.uiStructuresMap.set(propId, { siField, uiStructure });
			return;
		}

		const item = this.uiStructuresMap.get(propId);
		if (item === null) {
			return item;
		}

		item.uiStructure = this.deterOuter(item.uiStructure, uiStructure);
	}

	private deterOuter(uiStructure1: UiStructure, uiStructure2: UiStructure): UiStructure {
		if (uiStructure1 === uiStructure2) {
			return uiStructure1;
		}

		if (uiStructure1.containsDescendant(uiStructure2)) {
			return uiStructure1;
		}

		if (uiStructure2.containsDescendant(uiStructure1)) {
			return uiStructure2;
		}

		throw new Error('Impossible!');
	}

	fillToolbar() {
		for (const [, item] of this.uiStructuresMap) {
			item.uiStructure.createToolbarChild(item.siField.createUiStructureModel());
		}
	}
}
