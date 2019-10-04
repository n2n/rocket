import { SiComp } from 'src/app/si/model/entity/si-comp';
import { ViewContainerRef, ComponentFactoryResolver } from '@angular/core';
import { SiEntryDeclaration } from 'src/app/si/model/entity/si-entry-declaration';
import { SiEntry } from 'src/app/si/model/entity/si-entry';
import { BulkyEntryComponent } from 'src/app/ui/content/zone/comp/bulky-entry/bulky-entry.component';
import { SiFieldStructureDeclaration } from 'src/app/si/model/entity/si-field-structure-declaration';
import { UiContent } from 'src/app/si/model/structure/ui-content';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';
import { SiControl } from 'src/app/si/model/control/si-control';
import { TypeSiContent } from 'src/app/si/model/structure/impl/type-si-content';
import { Message } from 'src/app/util/i18n/message';

export class BulkyEntrySiComp implements SiComp {

	constructor(public entryDeclaration: SiEntryDeclaration) {
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


	getContents(): UiContent|null {
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
			siStructure: UiStructure) {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(BulkyEntryComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		componentRef.instance.model = this;
		componentRef.instance.siStructure = siStructure;

		return componentRef;
	}
}


