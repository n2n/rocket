import { Component, OnInit, DoCheck, Input, ViewChild, ComponentFactoryResolver, OnDestroy, ElementRef } from '@angular/core';
import { SiZone } from "src/app/si/model/structure/si-zone";
import { ListZoneContentComponent } from "src/app/ui/content/zone/comp/list-zone-content/list-zone-content.component";
import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";

@Component({
  selector: 'rocket-ui-zone',
  templateUrl: './zone.component.html',
  styleUrls: ['./zone.component.css']
})
export class ZoneComponent implements OnInit {

	@Input() siZone: SiZone;
	
	constructor(private componentFactoryResolver: ComponentFactoryResolver, private elemRef: ElementRef) { 
		elemRef.nativeElement.classList.add('rocket-contains-additional');
	}

	ngOnInit() {
//		const componentFactory = this.componentFactoryResolver.resolveComponentFactory(ListZoneContentComponent);
      
//	    const componentRef = this.zoneContentDirective.viewContainerRef.createComponent(componentFactory);
      
//      (<ZoneComponent> componentRef.instance).data = {};
	}
	
	get siStructure(): SiStructure|null {
		if (this.siZone.hasContent()) {
			return this.siZone.content.getStructure()
		} 
		
		return null;
	}
}

