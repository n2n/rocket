
import { ComponentFactoryResolver, ViewContainerRef, ComponentRef } from '@angular/core';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { SiZoneError } from 'src/app/si/model/structure/si-zone-error';

export interface SiContent {

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			commanderService: SiCommanderService): ComponentRef<any>;

	getZoneErrors(): SiZoneError[];

}
