import { ViewContainerRef, ComponentFactoryResolver } from '@angular/core';
import { EmbeddedEntriesInModel } from '../comp/embedded-entry-in-model';
import { SiEmbeddedEntry } from './si-embedded-entry';
import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { EmbeddedEntriesSummaryInComponent } from '../comp/embedded-entries-summary-in/embedded-entries-summary-in.component';
import { EmbeddedEntriesInComponent } from '../comp/embedded-entries-in/embedded-entries-in.component';
import { EmbeddedEntriesConfig } from './embedded-entries-config';
import { SiService } from 'src/app/si/manage/si.service';
import { EmbeddedAddPasteObtainer } from './embedded-add-paste-obtainer';
import { AddPasteObtainer } from '../comp/add-paste-obtainer';
import { EmbeddedEntryObtainer } from './embedded-entry-obtainer';

export class EmbeddedEntriesInUiContent implements EmbeddedEntriesInModel {

	constructor(public siService: SiService, public typeCategory: string, public apiUrl: string, 
			public values: SiEmbeddedEntry[] = [], private uiStructure: UiStructure, 
			private config: EmbeddedEntriesConfig) {
	}

	getApiUrl(): string {
		return this.apiUrl;
	}

	getValues(): SiEmbeddedEntry[] {
		return this.values;
	}

	setValues(values: SiEmbeddedEntry[]) {
		if (this.values === values) {
			return;
		}

		this.values.splice(0, this.values.length);
		this.values.push(...values);
	}

	getMin(): number {
		return this.config.min;
	}

	getMax(): number|null {
		return this.config.max;
	}

	isSummaryRequired(): boolean {
		return this.config.reduced;
	}

	isNonNewRemovable(): boolean {
		return this.config.nonNewRemovable;
	}

	isSortable(): boolean {
		return this.config.sortable;
	}

	getTypeCategory(): string {
		return this.typeCategory;
	}

	getAllowedSiTypeQualifiers(): SiTypeQualifier[]|null {
		return this.config.allowedSiTypeQualifiers;
	}

	getObtainer(): AddPasteObtainer {
		return new EmbeddedAddPasteObtainer(new EmbeddedEntryObtainer(this.siService, this.apiUrl, this.config.reduced));
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver/*,
			uiStructure: UiStructure*/) {
		if (this.config.reduced) {
			const componentFactory = componentFactoryResolver.resolveComponentFactory(EmbeddedEntriesSummaryInComponent);
			const componentRef = viewContainerRef.createComponent(componentFactory);
			componentRef.instance.model = this;
			componentRef.instance.uiStructure = this.uiStructure;
			return componentRef;
		} else {
			const componentFactory = componentFactoryResolver.resolveComponentFactory(EmbeddedEntriesInComponent);
			const componentRef = viewContainerRef.createComponent(componentFactory);
			componentRef.instance.model = this;
			componentRef.instance.uiStructure = this.uiStructure;
			return componentRef;
		}
	}
}
