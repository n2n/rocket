
import { ComponentFactoryResolver, ViewContainerRef, ComponentRef } from '@angular/core';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';

export interface UiContent {

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			siStructure: UiStructure): ComponentRef<any>;

// 	getZoneErrors(): UiZoneError[];

}
