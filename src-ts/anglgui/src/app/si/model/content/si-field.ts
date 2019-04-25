import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { SiInputValue } from "src/app/si/model/input/si-entry-input";

export interface SiField {
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver);
    
	hasInput(): boolean;
	
	readInput(): Map<string, SiInputValue>;
}
