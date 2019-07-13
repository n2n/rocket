import { SiFile } from "src/app/si/model/content/impl/file-in-si-field";
import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { OutSiFieldAdapter } from "src/app/si/model/content/impl/out-si-field-adapter";
import { FileFieldModel } from "src/app/ui/content/field/file-field-model";

export class FileOutSiField extends OutSiFieldAdapter implements FileFieldModel {
    
    constructor(public value: SiFile|null) {
		super();
	}
	
	getSiFile(): SiFile | null {
        return this.value;
    }
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver) {
//		const componentFactory = componentFactoryResolver.resolveComponentFactory(StringOutFieldComponent);
//	    const componentRef = viewContainerRef.createComponent(componentFactory);
//	    componentRef.instance.model = {
//	    	getValue() {
//	    		return 'file';
//	    	}
//	    }
//	    
//	    return componentRef;
    }
}