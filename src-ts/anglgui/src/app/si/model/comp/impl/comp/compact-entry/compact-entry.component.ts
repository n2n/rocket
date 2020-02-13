import { Component, OnInit, OnDestroy, DoCheck } from '@angular/core';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { CompactEntryModel } from '../compact-entry-model';

@Component({
	selector: 'rocket-compact-entry',
	templateUrl: './compact-entry.component.html',
	styleUrls: ['./compact-entry.component.css']
})
export class CompactEntryComponent implements OnInit, OnDestroy, DoCheck {
	uiStructure: UiStructure;
	model: CompactEntryModel;

	siEntry: SiEntry|null = null;
	fieldUiStructures: UiStructure[] = [];

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

	get loading(): boolean {
		return !this.siEntry;
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

		const siEntryBuildup = siEntry.selectedEntryBuildup;
		const siTypeDeclaration = this.model.getSiDeclaration().getTypeDeclarationByTypeId(siEntry.selectedTypeId);

		for (const siProp of siTypeDeclaration.getSiProps()) {
			const structure = this.uiStructure.createChild();
			structure.model = siEntryBuildup.getFieldById(siProp.id).createUiStructureModel();
			this.fieldUiStructures.push(structure);
		}
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
}
