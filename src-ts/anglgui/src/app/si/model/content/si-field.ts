import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";

export interface SiField {
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver);
    
	hasInput(): boolean;
	
	readInput(): object;
}
