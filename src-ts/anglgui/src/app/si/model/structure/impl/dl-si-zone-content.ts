import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";
import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { SiBulkyDeclaration } from "src/app/si/model/structure/si-bulky-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { DlZoneContentComponent } from "src/app/ui/content/zone/comp/dl-zone-content/dl-zone-content.component";

export class DlSiZoneContent implements SiZoneContent {
    public entries: SiEntry[] = [];
	
	constructor(public apiUrl: string, public bulkyDeclaration: SiBulkyDeclaration) {
		
	}
	
	getApiUrl(): string {
		return this.apiUrl;
	}
	
    getEntries(): SiEntry[] {
        throw new Error("Method not implemented.");
    }
    
    getSelectedEntries(): SiEntry[] {
        throw new Error("Method not implemented.");
    }
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver) {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(DlZoneContentComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.dlSiZoneContent = this;
	    
	    return componentRef;
	}
}