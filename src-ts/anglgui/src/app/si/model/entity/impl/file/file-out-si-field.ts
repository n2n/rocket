import { SiFile } from 'src/app/si/model/entity/impl/file/file-in-si-field';
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from '@angular/core';
import { OutSiFieldAdapter } from 'src/app/si/model/entity/impl/out-si-field-adapter';
import { FileFieldModel } from 'src/app/ui/content/field/file-field-model';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { SiZone } from '../../../structure/si-zone';
import { TypeSiContent } from "src/app/si/model/structure/impl/type-si-content";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { FileOutFieldComponent } from "src/app/ui/content/field/comp/file-out-field/file-out-field.component";

export class FileOutSiField extends OutSiFieldAdapter implements FileFieldModel {

	constructor(public zone: SiZone, public value: SiFile|null) {
		super();
	}

	getSiZone(): SiZone {
		return this.zone;
	}

	getSiFile(): SiFile | null {
		return this.value;
	}
    
    getContent(): SiContent|null {
        return new TypeSiContent(FileOutFieldComponent, (ref, structure) => {
//            ref.instance.model = this;
        });
    }

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			commanderService: SiCommanderService): ComponentRef<any> {
	// 		const componentFactory = componentFactoryResolver.resolveComponentFactory(StringOutFieldComponent);
	// 	    const componentRef = viewContainerRef.createComponent(componentFactory);
	// 	    componentRef.instance.model = {
	// 	    	getValue() {
	// 	    		return 'file';
	// 	    	}
	// 	    }
	//
	// 	    return componentRef;
		return undefined;
	}
}
