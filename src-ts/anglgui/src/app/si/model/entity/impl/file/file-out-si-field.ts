import { SiFile } from 'src/app/si/model/entity/impl/file/file-in-si-field';
import { ComponentRef } from '@angular/core';
import { OutSiFieldAdapter } from 'src/app/si/model/entity/impl/out-si-field-adapter';
import { FileFieldModel } from 'src/app/ui/content/field/file-field-model';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { TypeSiContent } from 'src/app/si/model/structure/impl/type-si-content';
import { FileOutFieldComponent } from 'src/app/ui/content/field/comp/file-out-field/file-out-field.component';

export class FileOutSiField extends OutSiFieldAdapter implements FileFieldModel {

	constructor(public value: SiFile|null) {
		super();
	}

	getSiFile(): SiFile | null {
		return this.value;
	}

	getContent(): SiContent|null {
		return new TypeSiContent(FileOutFieldComponent, () => {
//            ref.instance.model = this;
		});
	}

	initComponent(): ComponentRef<any> {
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
