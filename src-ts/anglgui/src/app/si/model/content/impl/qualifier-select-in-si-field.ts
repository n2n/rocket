
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { InputInFieldComponent } from "src/app/ui/content/field/comp/input-in-field/input-in-field.component";
import { StringInFieldModel } from "src/app/ui/content/field/string-in-field-model";
import { InSiFieldAdapter } from "src/app/si/model/content/impl/in-si-field-adapter";
import { SiQualifier } from "src/app/si/model/content/si-qualifier";

export class QualifierSelectInSiField extends InSiFieldAdapter implements StringInFieldModel {
    
    public min: number|null = null;
	public max: number|null = null;
	
	constructor(public apiUrl: string, public values: SiQualifier[] = []) {
		super();
		this.validate();
	}
	
    readInput(): object {
        return { 'values': this.values };
    }
	
    getValues(): SiQualifier[] {
    	return this.values;
    }
    
    setValues(values: SiQualifier[]) {
    	this.values = values;
    }
    
    getMaxlength(): number|null {
    	return this.maxlength;
    }
    
    setValue(value: string|null) {
    	this.value = value;
    	this.validate();
    }
    
    private validate() {
    	this.messages = [];
    	
    	if (this.mandatory && this.value === null) {
    		this.messages.push('mandatory front err');
    	}
    	
    	if (this.minlength && this.value && this.value.length < this.minlength) {
    		this.messages.push('minlength front err');
    	}
    	
    	if (this.maxlength && this.value && this.value.length > this.maxlength) {
    		this.messages.push('maxlength front err');
    	}
    }
    
	initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(InputInFieldComponent);
	    
	    const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    const component = componentRef.instance;
	    component.model = this;
	    
	    return componentRef;
	}
}