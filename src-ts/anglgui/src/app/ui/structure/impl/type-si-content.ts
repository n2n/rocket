import { UiContent } from '../si-content';
import { UiZoneError } from '../ui-zone-error';
import { ComponentFactoryResolver, ComponentRef, ViewContainerRef } from '@angular/core';
import { SiUiService } from '../../si-ui.service';
import { UiStructure } from "src/app/si/model/structure/ui-structure";


export class TypeSiContent<T> implements UiContent {
	public zoneErrors: UiZoneError[] = [];

	constructor(public type: new(...args: any[]) => T, public callback: (cr: ComponentRef<T>, ss: UiStructure) => any) {
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			siStructure: UiStructure): ComponentRef<T> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory<T>(this.type);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		this.callback(componentRef, siStructure);

		return componentRef;
	}

	getZoneErrors(): UiZoneError[] {
		return this.zoneErrors;
	}
}
