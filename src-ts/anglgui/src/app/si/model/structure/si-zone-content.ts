
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { SiEntry } from "src/app/si/model/content/si-entry";

export interface SiZoneContent {
 	
	getApiUrl(): string;
	
	getEntries(): SiEntry[];
	
	getSelectedEntries(): SiEntry[];
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver);
}
