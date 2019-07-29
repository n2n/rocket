
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from '@angular/core';
import { InSiFieldAdapter } from 'src/app/si/model/content/impl/in-si-field-adapter';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { EmbeddedEntryInFieldComponent } from 'src/app/ui/content/field/comp/embedded-entry-in-field/embedded-entry-in-field.component';
import { EmbeddedEntryInModel } from 'src/app/ui/content/field/embedded-entry-in-model';
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { SiField } from '../si-field';
import { SiType } from 'src/app/si/model/content/si-type';

export class EmbeddedEntryInSiField extends InSiFieldAdapter implements EmbeddedEntryInModel {

	public min = 0;
	public max: number|null = null;
	public reduced = false;
	public nonNewRemovable = true;
	public sortable = false;
	public pastCategory: string|null = null;
	public allowedSiTypes: SiType[]|null = null;

	private structures: SiStructure[] = [];

	constructor(public zone: SiZone, public apiUrl: string, public values: SiEmbeddedEntry[] = []) {
		super();
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

	registerSiStructure(siStructure: SiStructure) {
		this.structures.push(siStructure);
	}

	unregisterSiStructure(siStructure: SiStructure) {
		const i = this.structures.indexOf(siStructure);
		if (i > -1) {
			this.structures.splice(i, 1);
		}
	}

	getMin(): number {
		return this.min;
	}

	getMax(): number|null {
		return this.max;
	}

	isReduced(): boolean {
		return this.reduced;
	}

	isNonNewRemovable(): boolean {
		return this.nonNewRemovable;
	}

	isSortable(): boolean {
		return this.sortable;
	}

	getPastCategory(): string|null {
		return this.pastCategory;
	}

	getAllowedSiTypes(): SiType[]|null {
		return this.allowedSiTypes;
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			commanderService: SiCommanderService): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(EmbeddedEntryInFieldComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		const component = componentRef.instance;
		component.model = this;

		return componentRef;
	}
}
