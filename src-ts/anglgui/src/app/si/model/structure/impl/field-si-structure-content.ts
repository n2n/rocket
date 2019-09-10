
import { SiContent } from 'src/app/si/model/structure/si-content';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from '@angular/core';
import { FieldStructureComponent } from 'src/app/ui/structure/comp/field-structure/field-structure.component';
import { SiFieldDeclaration } from 'src/app/si/model/structure/si-field-declaration';
import { SiZoneError } from 'src/app/si/model/structure/si-zone-error';
import { SiField } from 'src/app/si/model/content/si-field';

export class FieldSiStructureContent implements SiContent {

	constructor(readonly entry: SiEntry, readonly fieldDeclaration: SiFieldDeclaration) {
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(FieldStructureComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		componentRef.instance.fieldSiStructureContent = this;

		return componentRef;
	}

	get field(): SiField|null {
		return this.entry.selectedTypeBuildup.getFieldById(this.fieldDeclaration.fieldId);
	}

	getZoneErrors(): SiZoneError[] {
		return this.entry.getZoneErrors();
	}
}
