
import { ComponentFactoryResolver, ViewContainerRef, ComponentRef } from "@angular/core";
import { SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";

export interface SiStructure {

	getType(): SiStructureType|null
	
	getLabel(): string|null;
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any>;
}
