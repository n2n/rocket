
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiFieldStructureDeclaration, SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { FieldStructureComponent } from "src/app/ui/structure/comp/field-structure/field-structure.component";

export class FieldSiStructure implements SiStructure {
    readonly children: FieldSiStructure[] = []; 

	constructor(readonly entry: SiEntry,
			readonly fieldStructureDeclaration: SiFieldStructureDeclaration) {
		for (let child of fieldStructureDeclaration.children) {
			this.children.push(new FieldSiStructure(this.entry, child));
    	}
	}
	
	getLabel(): string | null {
        return this.fieldStructureDeclaration.fieldDeclaration.label;
    }
	
	getType(): SiStructureType | null {
        return null;
    }
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(FieldStructureComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.fieldSiStructure = this;
	    
	    return componentRef;
	}
}