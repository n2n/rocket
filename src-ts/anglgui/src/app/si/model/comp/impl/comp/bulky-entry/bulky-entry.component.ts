import { Component, OnInit, OnDestroy, DoCheck } from '@angular/core';
import { BulkyEntryModel } from '../bulky-entry-model';
import { UiStructureDeclaration } from 'src/app/si/model/meta/si-structure-declaration';
import { SiUiStructureModelFactory } from '../../model/si-ui-structure-model-factory';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

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

	private createStructures(parent: UiStructure, uiStructureDeclarations: UiStructureDeclaration[]): UiStructure[] {
		const structures: UiStructure[] = [];
		for (const ssd of uiStructureDeclarations) {
			structures.push(this.dingsel(parent, ssd));
		}
		return structures;
	}

	private dingsel(parent: UiStructure, ssd: UiStructureDeclaration): UiStructure {
		const structure = parent.createChild();
		structure.label = ssd.prop ? ssd.prop.label : ssd.label;
		structure.type = ssd.type;

		if (ssd.prop) {
			structure.label = ssd.prop.label;
			structure.model = SiUiStructureModelFactory.createBulkyField(
					this.model.getSiEntry().selectedEntryBuildup.getFieldById(ssd.prop.id));
		} else {
			structure.label = ssd.label;
			structure.model = SiUiStructureModelFactory.createBulkyEmpty();
		}

		this.createStructures(structure, ssd.children);

		return structure;
	}

}
