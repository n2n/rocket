
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { InputInFieldComponent } from "src/app/ui/content/field/comp/input-in-field/input-in-field.component";
import { StringInFieldModel } from "src/app/ui/content/field/string-in-field-model";
import { InSiFieldAdapter } from "src/app/si/model/content/impl/in-si-field-adapter";
import { SiQualifier, SiIdentifier } from "src/app/si/model/content/si-qualifier";
import { QualifierSelectInModel } from "src/app/ui/content/field/qualifier-select-in-model";
import { QualifierSelectInFieldComponent } from "src/app/ui/content/field/comp/qualifier-select-in-field/qualifier-select-in-field.component";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { EmbeddedEntryInFieldComponent } from "src/app/ui/content/field/comp/embedded-entry-in-field/embedded-entry-in-field.component";
import { EmbeddedEntryInModel } from "src/app/ui/content/field/embedded-entry-in-model";
import { SiContent } from "src/app/si/model/structure/si-zone-content";
import { BulkyEntrySiContent } from "src/app/si/model/structure/impl/bulky-entry-si-content";
import { CompactEntrySiContent } from "src/app/si/model/structure/impl/compact-entry-si-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiEmbeddedEntry } from "src/app/si/model/content/si-embedded-entry";
import { SiCommanderService } from "src/app/si/model/si-commander.service";

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
		return { 'values': this.values };
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
	
	
	private validate() {
		this.messages = [];
		
		if (this.values.length < this.min) {
			this.messages.push('min front err');
		}
		
		if (this.max && this.values.length > this.max) {
			this.messages.push('max front err');
		}
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
