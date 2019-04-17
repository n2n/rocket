
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";

export class StringInSiField implements SiField {
	public mandatory: boolean = false;
	public maxlength: number|null = null;
	
	constructor(public value: string|null, readonly multiline: boolean = false) {
		
	}
		
	initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(StringOutFieldComponent);
	    
	    const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.value = this.value;
	    
	    return componentRef;
	}
}