import { SiContent } from "src/app/si/model/structure/si-zone-content";
import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { SiCompactDeclaration } from "src/app/si/model/structure/si-compact-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { BulkyEntryComponent } from "src/app/ui/content/zone/comp/bulky-entry/bulky-entry.component";
import { SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { FieldSiStructureContent } from "src/app/si/model/structure/impl/field-si-structure-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiZoneError } from "src/app/si/model/structure/si-zone-error";
import { SiStructureModel } from "src/app/si/model/structure/si-structure-model";
import { CompactEntryComponent } from "src/app/ui/content/zone/comp/compact-entry/compact-entry.component";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiCommanderService } from "src/app/si/model/si-commander.service";

export class CompactEntrySiContent implements SiContent {
    public entry: SiEntry|null = null;
	public controlMap: Map<string, SiControl> = new Map();
	
	constructor(public compactDeclaration: SiCompactDeclaration,
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
    
    getChildren(): SiStructure[] {
    	return [];
	}
    
    getControls(): SiControl[] {
    	const controls: SiControl[] = [];
		controls.push(...this.controlMap.values());
		controls.push(...this.entry.selectedBuildup.controlMap.values());
		return controls;
    }
	
	getFieldDeclarations(): SiFieldDeclaration[] {
		return this.compactDeclaration.getFieldDeclarationsByBuildupId(this.entry.selectedBuildupId);
	}
		
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver,
			commanderService: SiCommanderService) {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(CompactEntryComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.siContent = this;
	    
	    return componentRef;
	}
}