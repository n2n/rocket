
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";

export interface SiZoneContent {
 	
	getApiUrl(): string;
	
	getEntries(): SiEntry[];
	
	getSelectedEntries(): SiEntry[];
	
	getStructure(): SiStructure;
}
