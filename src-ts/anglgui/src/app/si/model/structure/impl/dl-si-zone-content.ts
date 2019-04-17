import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";
import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { SiBulkyDeclaration } from "src/app/si/model/structure/si-bulky-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";

export class DlSiZoneContent implements SiZoneContent {
    public entries: SiEntry[] = [];
	
	constructor(public apiUrl: string, public bulkyDeclaration: SiBulkyDeclaration) {
		
	}
	
	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver) {
//		const componentFactory = componentFactoryResolver.resolveComponentFactory(ListZoneContentComponent);
//	    
//	    return viewContainerRef.createComponent(componentFactory);
	}
}