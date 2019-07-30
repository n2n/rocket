
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { StringFieldModel } from "src/app/ui/content/field/string-field-model";
import { OutSiFieldAdapter } from "src/app/si/model/content/impl/out-si-field-adapter";
import { LinkOutModel } from "src/app/ui/content/field/link-field-model";
import { LinkOutFieldComponent } from "src/app/ui/content/field/comp/link-out-field/link-out-field.component";
import { SiCommanderService } from "src/app/si/model/si-commander.service";
import { SiContent } from "src/app/si/model/structure/si-content";

export class LinkOutSiField extends OutSiFieldAdapter implements LinkOutModel, SiContent {

    
	constructor(private href: boolean, private ref: string, private label: string) {
        super();
	}
	
	getContent(): SiContent|null {
		return this;
	}
	
	initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver,
			commanderService: SiCommanderService): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(LinkOutFieldComponent);
	    
	    const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.model = this;
	    
	    return componentRef;
	}
	
    isHref(): boolean {
        return this.href;
    }
    getRef(): string {
        return this.ref;
    }
    getLabel(): string {
        return this.label;
    }
}