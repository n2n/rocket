import { SiFile } from "src/app/si/model/content/impl/file-in-si-field";
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { OutSiFieldAdapter } from "src/app/si/model/content/impl/out-si-field-adapter";
import { FileFieldModel } from "src/app/ui/content/field/file-field-model";
import { SiCommanderService } from "src/app/si/model/si-commander.service";

export class FileOutSiField extends OutSiFieldAdapter implements FileFieldModel {
    
    constructor(public value: SiFile|null) {
		super();
	}
	
	getSiFile(): SiFile | null {
        return this.value;
    }
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			commanderService: SiCommanderService): ComponentRef<any> {
//		const componentFactory = componentFactoryResolver.resolveComponentFactory(StringOutFieldComponent);
//	    const componentRef = viewContainerRef.createComponent(componentFactory);
//	    componentRef.instance.model = {
//	    	getValue() {
//	    		return 'file';
//	    	}
//	    }
//	    
//	    return componentRef;
		return undefined
    }
}