import { ViewContainerRef, ComponentFactoryResolver } from '@angular/core';

export class BulkyEntrySiComp implements SiComp {

	constructor(public declaration: SiDeclaration) {
	}

	private _entry: SiEntry|null = null;
	public controls: Array<SiControl> = [];

	getEntries(): SiEntry[] {
		return [this.entry];
	}

	getMessages(): Message[] {
		return [];
	}

	getSelectedEntries(): SiEntry[] {
		return [];
	}

	reload() {
	}

	getContent() {
		return this;
	}

	get entry(): SiEntry {
		return this._entry;
	}

	set entry(entry: SiEntry) {
		this._entry = entry;
	}

	createUiStructureModel(): UiStructureModel {
		const uiStructureModel = new SimpleUiStructureModel(new TypeUiContent(BulkyEntryComponent, (ref, uiStructure) => {
			ref.instance.model = this;
			ref.instance.uiStructure = structure;
		});

		uiStructureModel.controls = this.getControls().map(control => control.createUiContent());

		return uiStructureModel;
	}

	getControls(): SiControl[] {
		const controls: SiControl[] = [];
		controls.push(...this.controls);
		controls.push(...this.entry.selectedTypeBuildup.controlMap.values());
		return controls;
	}

	getFieldStructureDeclarations(): SiFieldStructureDeclaration[] {
		return this.declaration.getFieldStructureDeclarationsByTypeId(this.entry.selectedTypeId);
	}


}


