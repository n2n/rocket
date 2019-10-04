import { SiComp } from 'src/app/si/model/entity/si-comp';
import { ViewContainerRef, ComponentFactoryResolver } from '@angular/core';
import { SiEntryDeclaration } from 'src/app/si/model/entity/si-entry-declaration';
import { SiEntry } from 'src/app/si/model/entity/si-entry';
import { UiContent } from 'src/app/si/model/structure/ui-content';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';
import { SiControl } from 'src/app/si/model/control/si-control';
import { UiZoneError } from 'src/app/si/model/structure/ui-zone-error';
import { CompactEntryComponent } from 'src/app/ui/content/zone/comp/compact-entry/compact-entry.component';
import { SiFieldDeclaration } from 'src/app/si/model/entity/si-field-declaration';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { Message } from 'src/app/util/i18n/message';

export class CompactEntrySiComp implements SiComp, UiContent {
	public entry: SiEntry|null = null;
	public controlMap: Map<string, SiControl> = new Map();

	constructor(public entryDeclaration: SiEntryDeclaration) {
	}

	getEntries(): SiEntry[] {
		return [this.entry];
	}

	getMessages(): Message[] {
		if (!this.entry) {
			return [];
		}

		return this.entry.getMessages();
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
			siStructure: UiStructure) {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(CompactEntryComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		componentRef.instance.siContent = this;
		componentRef.instance.siStructure = siStructure;

		return componentRef;
	}
}
