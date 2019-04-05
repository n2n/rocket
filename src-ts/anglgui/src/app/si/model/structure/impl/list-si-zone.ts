
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { ListZoneContentComponent } from "src/app/ui/content/zone/comp/list-zone-content/list-zone-content.component";

export class ListSiZone implements SiZone {
 
	constructor(public siFieldDeclarations: SiFieldDeclaration[], public siEntries: SiEntry[]|null) {
		
	}
	

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver): ComponentRef<ListZoneContentComponent> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(ListZoneContentComponent);
	    
	    const componentRef =  viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.listSiZone = this;
	    
	    return componentRef;
	}
	
}