import { Component, OnInit, DoCheck, Input, ViewChild, ComponentFactoryResolver, OnDestroy, ElementRef } from '@angular/core';
import { SiZone } from "src/app/si/model/structure/si-zone";
import { ListZoneContentComponent } from "src/app/ui/content/zone/comp/list-zone-content/list-zone-content.component";
import { SiContent } from "src/app/si/model/structure/si-zone-content";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiZoneError } from "src/app/si/model/structure/si-zone-error";

@Component({
  selector: 'rocket-ui-zone',
  templateUrl: './zone.component.html',
  styleUrls: ['./zone.component.css']
})
export class ZoneComponent implements OnInit, DoCheck {

	@Input() siZone: SiZone;
	
	siZoneErrors: SiZoneError[] = [];
	
	constructor(private componentFactoryResolver: ComponentFactoryResolver, private elemRef: ElementRef) { 
		
	}

	ngOnInit() {
//		const componentFactory = this.componentFactoryResolver.resolveComponentFactory(ListZoneContentComponent);
      
//	    const componentRef = this.zoneContentDirective.viewContainerRef.createComponent(componentFactory);
      
//      (<ZoneComponent> componentRef.instance).data = {};
	}
	
	ngDoCheck(){
		this.siZoneErrors = this.siZone.structure.getZoneErrors();
		
		if (this.hasSiZoneErrors()) {
			this.elemRef.nativeElement.classList.add('rocket-contains-additional');
		} else {
			this.elemRef.nativeElement.classList.remove('rocket-contains-additional');
		}
		 
	}
	
	hasSiZoneErrors() {
		return this.siZoneErrors.length > 0;
	}
	
	get siStructure(): SiStructure {
		return this.siZone.structure;
	}
}


