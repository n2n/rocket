import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";
import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { SiBulkyDeclaration } from "src/app/si/model/structure/si-bulky-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { DlZoneContentComponent } from "src/app/ui/content/zone/comp/dl-zone-content/dl-zone-content.component";
import { SiStructureType, SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { FieldSiStructure } from "src/app/si/model/structure/impl/field-si-structure";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";

export class DlSiZoneContent implements SiZoneContent {
    public entries: SiEntry[] = [];
	
	constructor(public apiUrl: string, public bulkyDeclaration: SiBulkyDeclaration) {
		
	}
	
	getLabel(): string|null {
		return this.fieldDeclaration ? this.fieldDeclaration.label : null;
	}
	
	getType(): SiStructureType|null {
		return null;
	}
	
	getApiUrl(): string {
		return this.apiUrl;
	}
	
    getEntries(): SiEntry[] {
    	return this.entries;
    }
    
    getSelectedEntries(): SiEntry[] {
        return [];
    }
    
    private fieldDeclaration: SiFieldDeclaration|null = null;
    private childStructures: SiStructure[] = [];
    
	refreshChildStructures() {
		this.fieldDeclaration = null;
		this.childStructures = [];
		
    	if (this.entries.length != 1) {
    		for (let entry of this.entries) {
    			this.childStructures.push(new FieldSiStructure(entry, this.getFieldStructureDeclaration(entry)));
    		}
    		return;
    	}
    	
    	const entry = this.entries[0]
    	const declaration = this.getFieldStructureDeclaration(entry);
    	this.fieldDeclaration = declaration.fieldDeclaration;
    	for (const child of declaration.children) {
    		this.childStructures.push(new FieldSiStructure(entry, child));
    	}
	}

	getFieldStructureDeclaration(entry: SiEntry): SiFieldStructureDeclaration {
		return this.bulkyDeclaration.getFieldStructureDeclarationByBuildupId(entry.selectedBuildupId);
	}
	
	getChildStructures(): SiStructure[] {
		return this.childStructures;
	}
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver) {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(DlZoneContentComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.dlSiZoneContent = this;
	    
	    return componentRef;
	}
}