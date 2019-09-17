
import { ComponentFactoryResolver, ViewContainerRef, ComponentRef } from '@angular/core';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { SiZoneError } from 'src/app/si/model/structure/si-zone-error';
import { SiStructure } from "src/app/si/model/structure/si-structure";

export interface SiContent {

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			siStructure: SiStructure): ComponentRef<any>;

//	getZoneErrors(): SiZoneError[];

}
