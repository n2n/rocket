
import { ComponentFactoryResolver, ViewContainerRef, ComponentRef } from "@angular/core";
import { SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiCommanderService } from "src/app/si/model/si-commander.service";

export interface SiStructureContent {

	initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver,
			commanderService: SiCommanderService): ComponentRef<any>;
}
