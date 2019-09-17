import { SiContent } from '../si-content';
import { SiZoneError } from '../si-zone-error';
import { ComponentFactoryResolver, ComponentRef, ViewContainerRef } from '@angular/core';
import { SiCommanderService } from '../../si-commander.service';
import { SiStructure } from "src/app/si/model/structure/si-structure";


export class TypeSiContent<T> implements SiContent {
	public zoneErrors: SiZoneError[] = [];

	constructor(public type: new(...args: any[]) => T, public callback: (cr: ComponentRef<T>, ss: SiStructure) => any) {
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			siStructure: SiStructure): ComponentRef<T> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory<T>(this.type);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		this.callback(componentRef, siStructure);

		return componentRef;
	}

	getZoneErrors(): SiZoneError[] {
		return this.zoneErrors;
	}
}
