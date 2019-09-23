import { ViewContainerRef, ComponentFactoryResolver } from '@angular/core';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { EmbeddedEntriesInModel } from 'src/app/ui/content/embedded/embedded-entry-in-model';
import { SiType } from 'src/app/si/model/entity/si-type';
import { SiEmbeddedEntry } from 'src/app/si/model/entity/impl/embedded/si-embedded-entry';
import { EmbeddedEntriesInComponent } from 'src/app/ui/content/embedded/comp/embedded-entries-in/embedded-entries-in.component';
import { EmbeddedEntriesSummaryInComponent } from 'src/app/ui/content/embedded/comp/embedded-entries-summary-in/embedded-entries-summary-in.component';

export class EmbeddedEntriesInSiContent implements SiContent, EmbeddedEntriesInModel {

	public min = 0;
	public max: number|null = null;
	public reduced = false;
	public nonNewRemovable = true;
	public sortable = false;
	public pasteCategory: string|null = null;
	public allowedSiTypes: SiType[]|null = null;

	constructor(public apiUrl: string, public values: SiEmbeddedEntry[] = []) {
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
		return this.pasteCategory;
	}

	getAllowedSiTypes(): SiType[]|null {
		return this.allowedSiTypes;
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			siStructure: SiStructure) {
		if (this.reduced) {
			const componentFactory = componentFactoryResolver.resolveComponentFactory(EmbeddedEntriesSummaryInComponent);
			const componentRef = viewContainerRef.createComponent(componentFactory);
			componentRef.instance.model = this;
			componentRef.instance.siStructure = siStructure;
			return componentRef;
		} else {
			const componentFactory = componentFactoryResolver.resolveComponentFactory(EmbeddedEntriesInComponent);
			const componentRef = viewContainerRef.createComponent(componentFactory);
			componentRef.instance.model = this;
			componentRef.instance.siStructure = siStructure;
			return componentRef;
		}
	}
}


