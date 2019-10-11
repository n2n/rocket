import { Component, OnInit, OnDestroy, DoCheck } from '@angular/core';
import { BulkyEntryModel } from '../bulky-entry-model';
import { SiStructureDeclaration } from 'src/app/si/model/meta/si-structure-declaration';
import { SiUiStructureModelFactory } from '../../model/si-ui-structure-model-factory';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { StructureBranchComponent } from 'src/app/ui/structure/comp/structure-branch/structure-branch.component';

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
		for (const ssd of uiStructureDeclarations) {
			structures.push(this.dingsel(parent, ssd));
		}
		return structures;
	}

	private dingsel(parent: UiStructure, ssd: SiStructureDeclaration): UiStructure {
		const uiStructure = parent.createChild();
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
