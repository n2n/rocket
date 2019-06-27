
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiZoneError } from "src/app/si/model/structure/si-zone-error";

export interface SiZoneContent {
 	
//	getZone(): SiZone;
	
	getEntries(): SiEntry[];
	
	getSelectedEntries(): SiEntry[];
	
	getStructure(): SiStructure;
	
	getZoneErrors(): SiZoneError[];
}
