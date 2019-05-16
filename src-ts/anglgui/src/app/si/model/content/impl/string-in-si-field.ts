
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { InputInFieldComponent } from "src/app/ui/content/field/comp/input-in-field/input-in-field.component";
import { StringInFieldModel } from "src/app/ui/content/field/string-in-field-model";
import { InSiFieldAdapter } from "src/app/si/model/content/impl/in-si-field-adapter";

export class StringInSiField extends InSiFieldAdapter implements StringInFieldModel {
    
    public mandatory: boolean = false;
	public maxlength: number|null = null;
	
	constructor(public value: string|null, public multiline: boolean = false) {
		super();
	}
	
    readInput(): object {
        return { 'value': this.value };
    }
	
    getValue(): string|null {
    	return this.value;
    }
    
    getMaxlength(): number|null {
    	return this.maxlength;
    }
    
    setValue(value: string | null) {
    	this.value = value;
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