
import { ComponentFactoryResolver, ViewContainerRef, ComponentRef } from '@angular/core';
import { UiStructure } from './ui-structure';

export interface UiContent {

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			uiStructure: UiStructure): ComponentRef<any>;

// 	getZoneErrors(): UiZoneError[];

}
