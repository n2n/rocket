
import { ComponentFactoryResolver, ViewContainerRef, ComponentRef } from "@angular/core";
import { SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";

export interface SiStructureContent {

	initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any>;
}
