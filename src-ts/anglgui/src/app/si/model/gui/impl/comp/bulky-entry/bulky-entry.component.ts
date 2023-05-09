import { Component } from '@angular/core';
import { BulkyEntryModel } from '../bulky-entry-model';
import { SiMaskQualifier } from 'src/app/si/model/meta/si-mask-qualifier';

@Component({
	selector: 'rocket-bulky-entry',
	templateUrl: './bulky-entry.component.html'
})
export class BulkyEntryComponent /*implements OnInit, OnDestroy, DoCheck*/ {
	public model!: BulkyEntryModel;

	get typeSelected(): boolean {
		return this.model.getSiEntry().entrySelected;
	}

	get choosableSiMaskQualifiers(): SiMaskQualifier[] {
		return this.model.getSiEntry().entryQualifiers.map(eq => eq.maskQualifier);
	}

	chooseSiMaskQualifier(siMaskQualifier: SiMaskQualifier) {
		this.model.getSiEntry().selectedMaskId = siMaskQualifier.identifier.id;
	}

	// constructor() { }

	// ngOnInit() {
	// 	// this.sync();
	// }

	// ngDoCheck() {
	// 	// this.sync();
	// }

	// ngOnDestroy() {
	// 	// this.clear();
	// }

	// // private sync() {
	// // 	const siValueBoundary = this.model.getSiEntry();
	// // 	if (this.siValueBoundary === siValueBoundary) {
	// // 		return;
	// // 	}

	// // 	this.clear();
	// // 	this.siValueBoundary = siValueBoundary;

	// // 	if (siValueBoundary === null) {
	// // 		return;
	// // 	}

	// // 	// new TypeSelect(siValueBoundary.maskQualifiers);

	// // 	const siMaskDeclaration = this.model.getSiDeclaration().getTypeDeclarationByTypeId(siValueBoundary.selectedTypeId);
	// // 	const toolbarResolver = new ToolbarResolver();

	// // 	this.contentUiStructures = this.createStructures(this.uiStructure, siMaskDeclaration.structureDeclarations, toolbarResolver);

	// // 	for (const prop of siMaskDeclaration.type.getProps()) {
	// // 		if (prop.dependantPropIds.length > 0 && siValueBoundary.selectedEntry.containsPropId(prop.id)) {
	// // 			toolbarResolver.fillContext(prop, siValueBoundary.selectedEntry.getFieldById(prop.id));
	// // 		}
	// // 	}
	// // }

	// // private clear() {
	// // 	if (!this.contentUiStructures) {
	// // 		return;
	// // 	}

	// // 	let uiStructure: UiStructure|null = null;
	// // 	while (uiStructure = this.contentUiStructures.pop()) {
	// // 		uiStructure.dispose();
	// // 	}
	// // }

	// // private createStructures(parent: UiStructure, uiStructureDeclarations: SiStructureDeclaration[],
	// // 		toolbarResolver: ToolbarResolver): UiStructure[] {
	// // 	const structures: UiStructure[] = [];
	// // 	for (const usd of uiStructureDeclarations) {
	// // 		structures.push(this.dingsel(parent, usd, toolbarResolver));
	// // 	}
	// // 	return structures;
	// // }

	// // private dingsel(parent: UiStructure, ssd: SiStructureDeclaration, toolbarResolver: ToolbarResolver): UiStructure {
	// // 	const uiStructure = parent.createContentChild();
	// // 	uiStructure.label = ssd.prop ? ssd.prop.label : ssd.label;
	// // 	uiStructure.type = ssd.type;

	// // 	if (ssd.prop) {
	// // 		uiStructure.label = ssd.prop.label;
	// // 		const siField = this.model.getSiEntry().selectedEntry.getFieldById(ssd.prop.id);
	// // 		uiStructure.model = siField.createUiStructureModel();
	// // 		toolbarResolver.register(ssd.prop.id, uiStructure);
	// // 		return uiStructure;
	// // 	}

	// // 	uiStructure.label = ssd.label;
	// // 	uiStructure.model = new SimpleUiStructureModel(
	// // 			new TypeUiContent(StructureBranchComponent, (ref) => {
	// // 				ref.instance.uiStructure = uiStructure;
	// // 			}));

	// // 	this.createStructures(uiStructure, ssd.children, toolbarResolver);

	// // 	return uiStructure;
	// // }
}
