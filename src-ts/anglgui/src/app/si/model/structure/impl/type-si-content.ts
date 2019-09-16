import { SiContent } from '../si-content';
import { SiZoneError } from '../si-zone-error';
import { ComponentFactoryResolver, ComponentRef, ViewContainerRef } from '@angular/core';
import { SiCommanderService } from '../../si-commander.service';


export class TypeSiContent<T> implements SiContent {
	public zoneErrors: SiZoneError[] = [];

	constructor(public type: new(...args: any[]) => T, public callback: (cr: ComponentRef<T>) => any) {
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			commanderService: SiCommanderService): ComponentRef<T> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory<T>(this.type);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		this.callback(componentRef);

		return componentRef;
	}

	getZoneErrors(): SiZoneError[] {
		return this.zoneErrors;
	}
}
