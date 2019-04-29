import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";
import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { SiBulkyDeclaration } from "src/app/si/model/structure/si-bulky-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { DlZoneContentComponent } from "src/app/ui/content/zone/comp/dl-zone-content/dl-zone-content.component";
import { SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";

export class DlSiZoneContent implements SiZoneContent {
    public entries: SiEntry[] = [];
	
	constructor(public apiUrl: string, public bulkyDeclaration: SiBulkyDeclaration) {
		
	}
	
	getLabel(): string|null {
		return null;
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
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver) {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(DlZoneContentComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.dlSiZoneContent = this;
	    
	    return componentRef;
	}
}