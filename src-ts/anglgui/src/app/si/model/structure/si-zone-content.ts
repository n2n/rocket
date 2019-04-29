
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiStructure } from "src/app/si/model/structure/si-structure";

export interface SiZoneContent extends SiStructure {
 	
	getApiUrl(): string;
	
	getEntries(): SiEntry[];
	
	getSelectedEntries(): SiEntry[];
	
}
