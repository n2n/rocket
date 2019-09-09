import { SiFile } from 'src/app/si/model/content/impl/file-in-si-field';
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from '@angular/core';
import { OutSiFieldAdapter } from 'src/app/si/model/content/impl/out-si-field-adapter';
import { FileFieldModel } from 'src/app/ui/content/field/file-field-model';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { SiZone } from '../../structure/si-zone';

export class FileOutSiField extends OutSiFieldAdapter implements FileFieldModel, SiContent {

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
		return this;
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
