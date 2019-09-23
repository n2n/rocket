import { SiComp } from 'src/app/si/model/entity/si-comp';
import { ViewContainerRef, ComponentFactoryResolver } from '@angular/core';
import { SiEntryDeclaration } from 'src/app/si/model/entity/si-entry-declaration';
import { SiEntry } from 'src/app/si/model/entity/si-entry';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiZoneError } from 'src/app/si/model/structure/si-zone-error';
import { CompactEntryComponent } from 'src/app/ui/content/zone/comp/compact-entry/compact-entry.component';
import { SiFieldDeclaration } from 'src/app/si/model/entity/si-field-declaration';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';

export class CompactEntrySiComp implements SiComp, SiContent {
	public entry: SiEntry|null = null;
	public controlMap: Map<string, SiControl> = new Map();

	constructor(public entryDeclaration: SiEntryDeclaration) {
	}

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

	getControls(): SiControl[] {
		const controls: SiControl[] = [];
		controls.push(...this.controlMap.values());
		controls.push(...this.entry.selectedTypeBuildup.controlMap.values());
		return controls;
	}

	getFieldDeclarations(): SiFieldDeclaration[] {
		return this.entryDeclaration.getFieldDeclarationsByTypeId(this.entry.selectedTypeId);
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			siStructure: SiStructure) {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(CompactEntryComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		componentRef.instance.siContent = this;
		componentRef.instance.siStructure = siStructure;

		return componentRef;
	}
}
