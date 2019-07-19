import { SiContent } from "src/app/si/model/structure/si-zone-content";
import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { SiBulkyDeclaration } from "src/app/si/model/structure/si-bulky-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { BulkyEntryComponent } from "src/app/ui/content/zone/comp/bulky-entry/bulky-entry.component";
import { SiStructureType, SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { FieldSiStructureContent } from "src/app/si/model/structure/impl/field-si-structure-content";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiZoneError } from "src/app/si/model/structure/si-zone-error";
import { SiStructureModel } from "src/app/si/model/structure/si-structure-model";

export class BulkyEntrySiContent implements SiContent {
   
    public entry: SiEntry|null = null;
	public controlMap: Map<string, SiControl> = new Map();
	
	constructor(public bulkyDeclaration: SiBulkyDeclaration,
			public zone: SiZone) {
	}
	
	getZone(): SiZone {
		return this.zone;
	}
	
    getEntries(): SiEntry[] {
    	return [this.entry];
    }
    
    getZoneErrors(): SiZoneError[] {
    	return [];
    }
    
    getSelectedEntries(): SiEntry[] {
        return [];
    }
    
    reload() {
	}
    
    getContent() {
    	return null;
    }
    
    private children: SiStructure[];
    
    getChildren(): SiStructure[] {
    	if (this.children) {
    		return this.children;
    	}
    	
		this.children = [];
    	const declarations = this.getFieldStructureDeclarations();
    	for (const child of declarations) {
    		this.children.push(this.dingsel(this.entry, child));
    	}
    	return this.children;
	}
    
    getControls(): SiControl[] {
    	const controls: SiControl[] = [];
		controls.push(...this.controlMap.values());
		controls.push(...this.entry.selectedBuildup.controlMap.values());
		return controls;
    }
	
	private dingsel(entry: SiEntry, fsd: SiFieldStructureDeclaration): SiStructure {
		const structure = new SiStructure();
		structure.label = fsd.fieldDeclaration.label;
		structure.type = fsd.type;
		
		const model = new FieldSiStructureModel(new FieldSiStructureContent(entry, fsd.fieldDeclaration));
		for (const childFsd of fsd.children) {
			model.children.push(this.dingsel(entry, childFsd));
		}
		
		structure.model = model; 
		return structure;
	}

	getFieldStructureDeclarations(): SiFieldStructureDeclaration[] {
		return this.bulkyDeclaration.getFieldStructureDeclarationsByBuildupId(this.entry.selectedBuildupId);
	}
		
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver) {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(BulkyEntryComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.siContent = this;
	    
	    return componentRef;
	}
}

class FieldSiStructureModel implements SiStructureModel {
	public children: SiStructure[] = [];
	
	constructor(public content: FieldSiStructureContent) {
	}
	
    getContent(): SiStructureContent {
    	return this.content;
    }
    getChildren(): SiStructure[] {
    	return this.children;
    }
    getControls(): SiControl[] {
        return [];
    }
    
    getZoneErrors(): SiZoneError[] {
		const field = this.content.field;
	 	if (field) {
	 		return field.getZoneErrors()
	 	}
		
		return [];
	}
}
