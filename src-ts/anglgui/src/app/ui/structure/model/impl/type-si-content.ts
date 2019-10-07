import { UiZoneError } from '../ui-zone-error';
import { ComponentFactoryResolver, ComponentRef, ViewContainerRef } from '@angular/core';
import { UiStructure } from '../ui-structure';
import { UiContent } from '../ui-content';

export class TypeUiContent<T> implements UiContent {
	public zoneErrors: UiZoneError[] = [];

	constructor(public type: new(...args: any[]) => T, public callback: (cr: ComponentRef<T>, ss: UiStructure) => any) {
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			uiStructure: UiStructure): ComponentRef<T> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory<T>(this.type);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		this.callback(componentRef, uiStructure);

		return componentRef;
	}

	getZoneErrors(): UiZoneError[] {
		return this.zoneErrors;
	}
}
