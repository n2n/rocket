
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from '@angular/core';
import { InSiFieldAdapter } from 'src/app/si/model/content/impl/in-si-field-adapter';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { EmbeddedEntryInFieldComponent } from 'src/app/ui/content/field/comp/embedded-entry-in-field/embedded-entry-in-field.component';
import { EmbeddedEntryInModel } from 'src/app/ui/content/field/embedded-entry-in-model';
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { SiField } from '../si-field';
import { EmbeddedAddPasteObtainer } from 'src/app/ui/content/field/comp/embedded-entry-in-field/embedded-add-paste-optainer';

export class EmbeddedEntryInSiField extends InSiFieldAdapter implements EmbeddedEntryInModel {
	public min = 0;
	public max: number|null = null;
	public nonNewRemovable = true;
	public reduced = false;

	private savedValues: SiEmbeddedEntry[];
	private structures: SiStructure[] = [];

	constructor(public zone: SiZone, public apiUrl: string, public values: SiEmbeddedEntry[] = []) {
		super();
		this.savedValues = values;
	}

	readInput(): object {
		return { values: this.values };
	}

	getSiZone(): SiZone {
		return this.zone;
	}

	getApiUrl(): string {
		return this.apiUrl;
	}

	getValues(): SiEmbeddedEntry[] {
		return this.values;
	}

	setValues(values: SiEmbeddedEntry[]) {
		this.values = values;
	}

	copy(): SiField {
		throw new Error('not yet implemented');
	}

	save() {
		this.savedValues = this.values;
	}

	reset() {
		this.values = this.savedValues;
	}

	registerSiStructure(siStructure: SiStructure) {
		this.structures.push(siStructure);
	}

	unregisterSiStructure(siStructure: SiStructure) {
		const i = this.structures.indexOf(siStructure);
		if (i > -1) {
			this.structures.splice(i, 1);
		}
	}

	getMax(): number|null {
		return this.max;
	}

	isReduced(): boolean {
		return this.reduced;
	}

	isNonNewRemovable() {
		return this.nonNewRemovable;
	}

	initComponent(viewContainerRef: ViewContainerRef,
			componentFactoryResolver: ComponentFactoryResolver,
			commanderService: SiCommanderService): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(EmbeddedEntryInFieldComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		const component = componentRef.instance;
		component.model = this;

		return componentRef;
	}
}
