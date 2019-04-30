
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { InputInFieldComponent } from "src/app/ui/content/field/comp/input-in-field/input-in-field.component";
import { SiInputValue } from "src/app/si/model/input/si-entry-input";

export class StringInSiField implements SiField {
	public mandatory: boolean = false;
	public maxlength: number|null = null;
	
	constructor(public value: string|null, readonly multiline: boolean = false) {
		
	}
		
	hasInput(): boolean {
		return true;
	}
	
    readInput(): Map<string, SiInputValue> {
        throw new Error("Method not implemented.");
    }
	
	
	initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(InputInFieldComponent);
	    
	    const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.value = this.value;
	    
	    return componentRef;
	}
}