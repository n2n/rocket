import { Component, OnInit, OnDestroy, DoCheck } from '@angular/core';
import { BulkyEntryModel } from '../bulky-entry-model';
import { SiStructureDeclaration } from 'src/app/si/model/meta/si-structure-declaration';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { StructureBranchComponent } from 'src/app/ui/structure/comp/structure-branch/structure-branch.component';
import { SiField } from 'src/app/si/model/content/si-field';
import { SiProp } from 'src/app/si/model/meta/si-prop';

@Component({
	selector: 'rocket-bulky-entry',
	templateUrl: './bulky-entry.component.html'
})
export class BulkyEntryComponent implements OnInit, OnDestroy, DoCheck {
	public model: BulkyEntryModel;
	public uiStructure: UiStructure;

	public siEntry: SiEntry|null = null;

	constructor() { }

	ngOnInit() {
		// this.sync();
	}

	ngDoCheck() {
		// this.sync();
	}

	ngOnDestroy() {
		// this.clear();
	}

	// private sync() {
	// 	const siEntry = this.model.getSiEntry();
	// 	if (this.siEntry === siEntry) {
	// 		return;
	// 	}

	// 	this.clear();
	// 	this.siEntry = siEntry;

	// 	if (siEntry === null) {
	// 		return;
	// 	}

	// 	// new TypeSelect(siEntry.typeQualifiers);

	// 	const siTypeDeclaration = this.model.getSiDeclaration().getTypeDeclarationByTypeId(siEntry.selectedTypeId);
	// 	const toolbarResolver = new ToolbarResolver();

	// 	this.contentUiStructures = this.createStructures(this.uiStructure, siTypeDeclaration.structureDeclarations, toolbarResolver);

	// 	for (const prop of siTypeDeclaration.type.getProps()) {
	// 		if (prop.dependantPropIds.length > 0 && siEntry.selectedEntryBuildup.containsPropId(prop.id)) {
	// 			toolbarResolver.fillContext(prop, siEntry.selectedEntryBuildup.getFieldById(prop.id));
	// 		}
	// 	}
	// }

	// private clear() {
	// 	if (!this.contentUiStructures) {
	// 		return;
	// 	}

	// 	let uiStructure: UiStructure|null = null;
	// 	while (uiStructure = this.contentUiStructures.pop()) {
	// 		uiStructure.dispose();
	// 	}
	// }

	// private createStructures(parent: UiStructure, uiStructureDeclarations: SiStructureDeclaration[],
	// 		toolbarResolver: ToolbarResolver): UiStructure[] {
	// 	const structures: UiStructure[] = [];
	// 	for (const usd of uiStructureDeclarations) {
	// 		structures.push(this.dingsel(parent, usd, toolbarResolver));
	// 	}
	// 	return structures;
	// }

	// private dingsel(parent: UiStructure, ssd: SiStructureDeclaration, toolbarResolver: ToolbarResolver): UiStructure {
	// 	const uiStructure = parent.createContentChild();
	// 	uiStructure.label = ssd.prop ? ssd.prop.label : ssd.label;
	// 	uiStructure.type = ssd.type;

	// 	if (ssd.prop) {
	// 		uiStructure.label = ssd.prop.label;
	// 		const siField = this.model.getSiEntry().selectedEntryBuildup.getFieldById(ssd.prop.id);
	// 		uiStructure.model = siField.createUiStructureModel();
	// 		toolbarResolver.register(ssd.prop.id, uiStructure);
	// 		return uiStructure;
	// 	}

	// 	uiStructure.label = ssd.label;
	// 	uiStructure.model = new SimpleUiStructureModel(
	// 			new TypeUiContent(StructureBranchComponent, (ref) => {
	// 				ref.instance.uiStructure = uiStructure;
	// 			}));

	// 	this.createStructures(uiStructure, ssd.children, toolbarResolver);

	// 	return uiStructure;
	// }
}
