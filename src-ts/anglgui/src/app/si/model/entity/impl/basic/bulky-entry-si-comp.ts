import { SiComp } from 'src/app/si/model/entity/si-comp';
import { ViewContainerRef, ComponentFactoryResolver } from '@angular/core';
import { SiEntryDeclaration } from 'src/app/si/model/entity/si-entry-declaration';
import { SiEntry } from 'src/app/si/model/entity/si-entry';
import { BulkyEntryComponent } from 'src/app/ui/content/zone/comp/bulky-entry/bulky-entry.component';
import { SiFieldStructureDeclaration } from 'src/app/si/model/entity/si-field-structure-declaration';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { SiZoneError } from 'src/app/si/model/structure/si-zone-error';
import { SiStructureModel } from 'src/app/si/model/structure/si-structure-model';
import { TypeSiContent } from 'src/app/si/model/structure/impl/type-si-content';
import { SimpleSiStructureModel } from 'src/app/si/model/structure/impl/simple-si-structure-model';
import { StructureBranchComponent } from 'src/app/ui/content/zone/comp/structure-branch/structure-branch.component';

export class BulkyEntrySiComp implements SiComp {

	constructor(public entryDeclaration: SiEntryDeclaration) {
	}

	private _entry: SiEntry|null = null;
	public controls: Array<SiControl> = [];

	private children: SiStructure[];

	getEntries(): SiEntry[] {
		return [this.entry];
	}

	getZoneErrors(): SiZoneError[] {
		if (!this.entry) {
			return [];
		}

		return this.entry.getZoneErrors();
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
		this.recheck();
	}

	recheck() {
		return this.children = null;
	}

	getContents(): SiContent|null {
		return new TypeSiContent(BulkyEntryComponent, (ref, structure) => {
			ref.instance.model = this;
			ref.instance.siStructure = structure;
		});
	}

	getControls(): SiControl[] {
		const controls: SiControl[] = [];
		controls.push(...this.controls);
		controls.push(...this.entry.selectedTypeBuildup.controlMap.values());
		return controls;
	}

	getFieldStructureDeclarations(): SiFieldStructureDeclaration[] {
		return this.entryDeclaration.getFieldStructureDeclarationsByTypeId(this.entry.selectedTypeId);
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			siStructure: SiStructure) {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(BulkyEntryComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		componentRef.instance.model = this;
		componentRef.instance.siStructure = siStructure;

		return componentRef;
	}
}

class FieldSiStructureModel implements SiStructureModel {
	public children: SiStructure[] = [];

	constructor(public content: SiContent|null) {
	}

	getContent(): SiContent|null {
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
