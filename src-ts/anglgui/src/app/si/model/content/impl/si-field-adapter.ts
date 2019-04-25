
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

export abstract class OutSiFieldAdapter implements SiField {
    abstract initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver);
    
	
	hasInput(): boolean {
		return false;
	}
	
	readInput(): Map<string, string | number | boolean | File | null> {
        throw new IllegalSiStateError('no input');
    }
		
	
}