
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { StringFieldModel } from "src/app/ui/content/field/string-field-model";
import { OutSiFieldAdapter } from "src/app/si/model/content/impl/out-si-field-adapter";

export class StringOutSiField extends OutSiFieldAdapter implements StringFieldModel {
    
	constructor(private value: string|null) {
        super();
	}
	
	initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(StringOutFieldComponent);
	    
	    const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.model = this;
	    
	    return componentRef;
	}
	
	getValue(): string | null {
        return this.getValue();
    }
}