import { SiFile } from "src/app/si/model/content/impl/file-in-si-field";
import { OutSiFieldAdapter } from "src/app/si/model/content/impl/si-field-adapter";
import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";

export class FileOutSiField extends OutSiFieldAdapter {
	
	constructor(public value: SiFile|null) {
		super();
	}
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver) {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(StringOutFieldComponent);
	    const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.value = this.value ? this.value.name : null;
	    
	    return componentRef;
    }
}