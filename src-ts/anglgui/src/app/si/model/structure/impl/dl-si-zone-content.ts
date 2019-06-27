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
	private structure = new SiStructure();
	public controlMap: Map<string, SiControl> = new Map();
	
	constructor(public apiUrl: string, public bulkyDeclaration: SiBulkyDeclaration,
			public zone: SiZone) {
		this.structure.content = this;
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
    
    getStructure(): SiStructure {
        return this.structure;
    }
    
	refreshChildStructures() {
		this.structure.clearChildren();
		
		if (this.entries.length != 1) {
    		for (let entry of this.entries) {
    			const fsd = this.getFieldStructureDeclaration(entry);
    			
    			this.structure.addChild(this.dingsel(entry, fsd));
    		}
    		return;
    	}
    	
    	const entry = this.entries[0]
    	const declaration = this.getFieldStructureDeclaration(entry);
    	this.structure.label = declaration.fieldDeclaration.label;
    	for (const child of declaration.children) {
    		this.structure.addChild(this.dingsel(entry, child));
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

	getFieldStructureDeclaration(entry: SiEntry): SiFieldStructureDeclaration {
		return this.bulkyDeclaration.getFieldStructureDeclarationByBuildupId(entry.selectedBuildupId);
	}
		
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver) {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(DlZoneContentComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.dlSiZoneContent = this;
	    
	    return componentRef;
	}
}