
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiZone } from "src/app/si/model/structure/si-zone";

export interface SiZoneContent {
 	
	getZone(): SiZone;
	
	getApiUrl(): string;
	
	getEntries(): SiEntry[];
	
	getSelectedEntries(): SiEntry[];
	
	getStructure(): SiStructure;
}