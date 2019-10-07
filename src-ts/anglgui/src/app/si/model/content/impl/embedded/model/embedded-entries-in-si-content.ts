import { ViewContainerRef, ComponentFactoryResolver } from '@angular/core';
import { EmbeddedEntriesInModel } from '../comp/embedded-entry-in-model';
import { SiEmbeddedEntry } from './si-embedded-entry';
import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { EmbeddedEntriesSummaryInComponent } from '../comp/embedded-entries-summary-in/embedded-entries-summary-in.component';
import { EmbeddedEntriesInComponent } from '../comp/embedded-entries-in/embedded-entries-in.component';

export class EmbeddedEntriesInSiContent implements EmbeddedEntriesInModel {

	public min = 0;
	public max: number|null = null;
	public reduced = false;
	public nonNewRemovable = true;
	public sortable = false;
	public pasteCategory: string|null = null;
	public allowedSiTypeQualifiers: SiTypeQualifier[]|null = null;

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

	getAllowedSiTypeQualifiers(): SiTypeQualifier[]|null {
		return this.allowedSiTypeQualifiers;
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			uiStructure: UiStructure) {
		if (this.reduced) {
			const componentFactory = componentFactoryResolver.resolveComponentFactory(EmbeddedEntriesSummaryInComponent);
			const componentRef = viewContainerRef.createComponent(componentFactory);
			componentRef.instance.model = this;
			componentRef.instance.uiStructure = uiStructure;
			return componentRef;
		} else {
			const componentFactory = componentFactoryResolver.resolveComponentFactory(EmbeddedEntriesInComponent);
			const componentRef = viewContainerRef.createComponent(componentFactory);
			componentRef.instance.model = this;
			componentRef.instance.uiStructure = uiStructure;
			return componentRef;
		}
	}
}


