import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { SiFieldError } from "src/app/si/model/input/si-field-error";
import { SiZoneError } from "src/app/si/model/structure/si-zone-error";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";

export interface SiField extends SiStructureContent {
	
	hasInput(): boolean;
	
	readInput(): object;
	
	handleError(error: SiFieldError): void;
	
	resetError(): void;
}
