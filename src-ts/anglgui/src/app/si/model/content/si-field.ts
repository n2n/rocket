import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { SiFieldError } from "src/app/si/model/input/si-field-error";

export interface SiField {
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver);
    
	hasInput(): boolean;
	
	readInput(): object;
	
	handleError(error: SiFieldError): void;
	
	resetError(): void;
}
