
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiFieldStructureDeclaration, SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { FieldStructureComponent } from "src/app/ui/structure/comp/field-structure/field-structure.component";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";

export class FieldSiStructureContent implements SiStructureContent {

	constructor(readonly entry: SiEntry,
			readonly fieldDeclaration: SiFieldDeclaration,
			readonly children: SiStructure[]) {
	}
	
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(FieldStructureComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.fieldSiStructureContent = this;
	    
	    return componentRef;
	}
}