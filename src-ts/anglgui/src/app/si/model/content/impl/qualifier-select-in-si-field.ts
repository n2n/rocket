
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { InputInFieldComponent } from "src/app/ui/content/field/comp/input-in-field/input-in-field.component";
import { StringInFieldModel } from "src/app/ui/content/field/string-in-field-model";
import { InSiFieldAdapter } from "src/app/si/model/content/impl/in-si-field-adapter";
import { SiQualifier } from "src/app/si/model/content/si-qualifier";
import { QualifierSelectInModel } from "src/app/ui/content/field/qualifier-select-in-model";
import { QualifierSelectInFieldComponent } from "src/app/ui/content/field/comp/qualifier-select-in-field/qualifier-select-in-field.component";
import { SiZone } from "src/app/si/model/structure/si-zone";

export class QualifierSelectInSiField extends InSiFieldAdapter implements QualifierSelectInModel {
	
	public min: number = 0;
	public max: number|null = null;
	
	constructor(public zone: SiZone, public apiUrl: string, public values: SiQualifier[] = []) {
		super();
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
	
	getValues(): SiQualifier[] {
		return this.values;
	}
	
	setValues(values: SiQualifier[]) {
		this.values = values;
    	this.validate();
	}
	
	getMin(): number {
		return this.min
	}
	
	getMax(): number|null {
		return this.max;
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
			componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(QualifierSelectInFieldComponent);
		
		const componentRef = viewContainerRef.createComponent(componentFactory);
		
		const component = componentRef.instance;
		component.model = this;
		
		return componentRef;
	}
}