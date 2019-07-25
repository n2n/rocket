import { SiComp } from 'src/app/si/model/structure/si-zone-content';
import { ViewContainerRef, ComponentFactoryResolver } from '@angular/core';
import { SiBulkyDeclaration } from 'src/app/si/model/structure/si-bulky-declaration';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { BulkyEntryComponent } from 'src/app/ui/content/zone/comp/bulky-entry/bulky-entry.component';
import { SiFieldStructureDeclaration } from 'src/app/si/model/structure/si-field-structure-declaration';
import { SiStructureContent } from 'src/app/si/model/structure/si-structure-content';
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { SiZoneError } from 'src/app/si/model/structure/si-zone-error';
import { SiStructureModel } from 'src/app/si/model/structure/si-structure-model';

export class BulkyEntrySiComp implements SiComp {

	constructor(public bulkyDeclaration: SiBulkyDeclaration,
			public zone: SiZone) {
	}

	public entry: SiEntry|null = null;
	public controls: Array<SiControl> = [];

	private children: SiStructure[];

	getZone(): SiZone {
		return this.zone;
	}

	getEntries(): SiEntry[] {
		return [this.entry];
	}

	getZoneErrors(): SiZoneError[] {
		return [];
	}

	getSelectedEntries(): SiEntry[] {
		return [];
	}

	reload() {
	}

	getContent() {
		return null;
	}

	getChildren(): SiStructure[] {
		if (this.children) {
			return this.children;
		}

		this.children = [];
		const declarations = this.getFieldStructureDeclarations();
		for (const child of declarations) {
			this.children.push(this.dingsel(this.entry, child));
		}
		return this.children;
	}

	getControls(): SiControl[] {
		const controls: SiControl[] = [];
		controls.push(...this.controls);
		controls.push(...this.entry.selectedTypeBuildup.controlMap.values());
		return controls;
	}

	private dingsel(entry: SiEntry, fsd: SiFieldStructureDeclaration): SiStructure {
		const structure = new SiStructure();
		structure.label = fsd.fieldDeclaration.label;
		structure.type = fsd.type;

		const model = new FieldSiStructureModel(entry.selectedTypeBuildup.getFieldById(fsd.fieldDeclaration.fieldId));
		for (const childFsd of fsd.children) {
			model.children.push(this.dingsel(entry, childFsd));
		}

		structure.model = model;
		return structure;
	}

	getFieldStructureDeclarations(): SiFieldStructureDeclaration[] {
		return this.bulkyDeclaration.getFieldStructureDeclarationsByBuildupId(this.entry.selectedTypeId);
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver) {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(BulkyEntryComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		componentRef.instance.siContent = this;

		return componentRef;
	}
}

class FieldSiStructureModel implements SiStructureModel {
	public children: SiStructure[] = [];

	constructor(public content: SiStructureContent) {
	}

	getContent(): SiStructureContent {
		return this.content;
	}

	getChildren(): SiStructure[] {
		return this.children;
	}

	getControls(): SiControl[] {
		return [];
	}

	getZoneErrors(): SiZoneError[] {
		return [];
	}
}
