import { Component, OnInit, OnDestroy } from '@angular/core';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { BulkyEntrySiComp } from 'src/app/si/model/content/impl/basic/bulky-entry-si-comp';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';
import { SiFieldStructureDeclaration } from 'src/app/si/model/content/si-field-structure-declaration';
import { SimpleUiStructureModel } from 'src/app/si/model/structure/impl/simple-ui-structure-model';
import { TypeUiContent } from 'src/app/si/model/structure/impl/type-si-content';
import { StructureBranchComponent } from 'src/app/ui/content/zone/comp/structure-branch/structure-branch.component';
import { BulkyEntryModel } from '../bulky-entry-model';
import { SiProp } from 'src/app/si/model/meta/si-prop';
import { SiStructureDeclaration } from 'src/app/si/model/meta/si-structure-declaration';
import { SiUiStructureModelFactory } from '../../model/si-ui-structure-model-factory';

@Component({
	selector: 'rocket-bulky-entry',
	templateUrl: './bulky-entry.component.html'
})
export class BulkyEntryComponent implements OnInit, OnDestroy {

	public model: BulkyEntryModel;
	public uiStructure: UiStructure;

	public fieldSiStructures: UiStructure[];

	constructor() { }

	ngOnInit() {
		this.fieldSiStructures = this.createStructures(this.uiStructure, this.model.getSiStructureDeclarations());
	}

	ngOnDestroy() {
		let uiStructure: UiStructure|null = null;
		while (uiStructure = this.fieldSiStructures.pop()) {
			uiStructure.dispose();
		}
	}

	get siEntry(): SiEntry {
		return this.model.entry;
	}

	private createStructures(parent: UiStructure, siStructureDeclarations: SiStructureDeclaration[]): UiStructure[] {
		const structures: UiStructure[] = [];
		for (const ssd of siStructureDeclarations) {
			structures.push(this.dingsel(parent, ssd));
		}
		return structures;
	}

// 	getChildren(): SiStructure[] {
// 			if (this.children) {
// 					return this.children;
// 			}
//
// 			this.children = [];
// 			const declarations = this.getFieldStructureDeclarations();
// 			for (const child of declarations) {
// 					this.children.push(this.dingsel(this.entry, child));
// 			}
// 			return this.children;
// 	}

	private dingsel(parent: UiStructure, ssd: SiStructureDeclaration): UiStructure {
		const structure = parent.createChild();
		structure.label = ssd.prop ? ssd.prop.label : ssd.label;
		structure.type = ssd.type;

		if (ssd.prop) {
			structure.label = ssd.prop.label;
			structure.model = SiUiStructureModelFactory.createBulkyField(
				this.model.getSiEntryBuildup().getFieldById(ssd.prop.id));
		} else {
			structure.label = ssd.label;
			structure.model = SiUiStructureModelFactory.createBulkyEmpty();
		}

		this.createStructures(structure, ssd.children);

		return structure;
	}

}
