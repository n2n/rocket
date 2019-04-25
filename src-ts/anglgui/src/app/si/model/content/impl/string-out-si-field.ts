
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { OutSiFieldAdapter } from "src/app/si/model/content/impl/si-field-adapter";

export class StringOutSiField extends OutSiFieldAdapter {
    
	constructor(private value: string|null) {
        super();
	}
	
	initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(StringOutFieldComponent);
	    
	    const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.value = this.value;
	    
	    return componentRef;
	}
}