
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiFieldStructureDeclaration, SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { FieldStructureComponent } from "src/app/ui/structure/comp/field-structure/field-structure.component";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiZoneError } from "src/app/si/model/structure/si-zone-error";

export class FieldSiStructureContent implements SiStructureContent {

	constructor(readonly entry: SiEntry,
			readonly fieldDeclaration: SiFieldDeclaration) {
	}
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(FieldStructureComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);
		
		componentRef.instance.fieldSiStructureContent = this;
		
		return componentRef;
	}

	 getZoneErrors(): SiZoneError[] {
		let zoneErrors: SiZoneError[] = [];
    		
		for (let [key, siField] of this.entry.selectedBuildup.fieldMap) {
			zoneErrors.push(...siField.getZoneErrors());
		}
    	
    	return zoneErrors;
    }
}