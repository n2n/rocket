import { ViewContainerRef, ComponentFactoryResolver } from '@angular/core';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { SiZoneError } from 'src/app/si/model/structure/si-zone-error';
import { EmbeddedEntryInModel } from 'src/app/ui/content/embedded/embedded-entry-in-model';
import { SiType } from 'src/app/si/model/entity/si-type';
import { SiEmbeddedEntry } from 'src/app/si/model/entity/si-embedded-entry';
import { EmbeddedEntriesInComponent } from 'src/app/ui/content/embedded/comp/embedded-entries-in/embedded-entries-in.component';
import { EmbeddedEntriesSummaryInComponent } from 'src/app/ui/content/embedded/comp/embedded-entries-summary-in/embedded-entries-summary-in.component';

export class EmbeddedEntriesInSiContent implements SiContent, EmbeddedEntryInModel {

	public min = 0;
	public max: number|null = null;
	public reduced = false;
	public nonNewRemovable = true;
	public sortable = false;
	public pastCategory: string|null = null;
	public allowedSiTypes: SiType[]|null = null;

	private structures: SiStructure[] = [];

	constructor(public zone: SiZone, public apiUrl: string, public values: SiEmbeddedEntry[] = []) {
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

	isSummaryRequired(): boolean {
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

	getZoneErrors(): SiZoneError[] {
		const errors: SiZoneError[] = [];

		for (const structure of this.structures) {
			if (!structure.model) {
				continue;
			}

			const content = structure.model.getContent();
			if (content) {
				errors.push(...content.getZoneErrors());
			}
		}

		return errors;
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver) {
		if (this.reduced) {
			const componentFactory = componentFactoryResolver.resolveComponentFactory(EmbeddedEntriesSummaryInComponent);
			const componentRef = viewContainerRef.createComponent(componentFactory);
			componentRef.instance.model = this;
			return componentRef;
		} else {
			const componentFactory = componentFactoryResolver.resolveComponentFactory(EmbeddedEntriesInComponent);
			const componentRef = viewContainerRef.createComponent(componentFactory);
			componentRef.instance.model = this;
			return componentRef;
		}
	}
}


