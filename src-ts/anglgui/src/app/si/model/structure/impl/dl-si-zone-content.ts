import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";
import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { SiBulkyDeclaration } from "src/app/si/model/structure/si-bulky-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { DlZoneContentComponent } from "src/app/ui/content/zone/comp/dl-zone-content/dl-zone-content.component";
import { SiStructureType, SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { FieldSiStructureContent } from "src/app/si/model/structure/impl/field-si-structure-content";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiZoneError } from "src/app/si/model/structure/si-zone-error";

export class DlSiZoneContent implements SiZoneContent, SiStructureContent {
   
    public entries: SiEntry[] = [];
	public controlMap: Map<string, SiControl> = new Map();
	
	constructor(public apiUrl: string, public bulkyDeclaration: SiBulkyDeclaration,
			public zone: SiZone) {
	}
	
	getZone(): SiZone {
		return this.zone;
	}
	
	getApiUrl(): string {
		return this.apiUrl;
	}
	
    getEntries(): SiEntry[] {
    	return this.entries;
    }
    
    getZoneErrors(): SiZoneError[] {
    	let zoneErrors: SiZoneError[] = [];
    		
    	for (let entry of this.entries) {
    		for (let [key, siField] of entry.selectedBuildup.fieldMap) {
    			zoneErrors.push(...siField.getZoneErrors());
    		}
    	}
    	
    	return zoneErrors;
    }
    
    getSelectedEntries(): SiEntry[] {
        return [];
    }
    
    reload() {
	}
    
    applyTo(structure: SiStructure) {
    	structure.clearChildren();
		
		for (let entry of this.entries) {
			const declarations = this.getFieldStructureDeclarations(entry);
	    	for (const child of declarations) {
	    		structure.addChild(this.dingsel(entry, child));
	    	}
		}
	}
	
	private dingsel(entry: SiEntry, fsd: SiFieldStructureDeclaration): SiStructure {
		const structure = new SiStructure();
		structure.label = fsd.fieldDeclaration.label;
		structure.type = fsd.type;
		
		for (const childFsd of fsd.children) {
			structure.addChild(this.dingsel(entry, childFsd));
		}
		
		structure.content = new FieldSiStructureContent(entry, fsd.fieldDeclaration);
		return structure;
	}

	getFieldStructureDeclarations(entry: SiEntry): SiFieldStructureDeclaration[] {
		return this.bulkyDeclaration.getFieldStructureDeclarationsByBuildupId(entry.selectedBuildupId);
	}
		
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver) {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(DlZoneContentComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.dlSiZoneContent = this;
	    
	    return componentRef;
	}
}