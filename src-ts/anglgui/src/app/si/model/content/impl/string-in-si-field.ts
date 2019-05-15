
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { InputInFieldComponent } from "src/app/ui/content/field/comp/input-in-field/input-in-field.component";

export class StringInSiField implements SiField {
	public mandatory: boolean = false;
	public maxlength: number|null = null;
	
	constructor(public value: string|null, public multiline: boolean = false) {
		
	}
		
	hasInput(): boolean {
		return true;
	}
	
    readInput(): object {
        return { 'value': this.value };
    }
	
	initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(InputInFieldComponent);
	    
	    const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    const component = componentRef.instance;
	    component.value = this.value;
	    component.mandatory = this.mandatory;
	    component.maxlength = this.maxlength;
	    
	    component.value$.subscribe(value => {
	    	this.value = value;
	    });
	    
	    return componentRef;
	}
}