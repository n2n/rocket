
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";

export interface SiZoneContent {
 	
	getApiUrl(): string;
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver);
}
