import { Component, OnInit, DoCheck, Input, ViewChild, ComponentFactoryResolver, OnDestroy } from '@angular/core';
import { SiZone } from "src/app/si/model/structure/si-zone";
import { ZoneContentDirective } from "src/app/ui/structure/comp/zone/zone-content.directive";
import { ListZoneContentComponent } from "src/app/ui/content/zone/comp/list-zone-content/list-zone-content.component";
import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";

@Component({
  selector: 'rocket-ui-zone',
  templateUrl: './zone.component.html',
  styleUrls: ['./zone.component.css']
})
export class ZoneComponent implements OnInit, DoCheck {

	@Input() siZone: SiZone;
	
	private currentSiZoneContent: SiZoneContent|null = null;
	
    @ViewChild(ZoneContentDirective) zoneContentDirective: ZoneContentDirective;
	
    constructor(private componentFactoryResolver: ComponentFactoryResolver) { }

	ngOnInit() {
		console.log("new zone¨!!");
		
//		const componentFactory = this.componentFactoryResolver.resolveComponentFactory(ListZoneContentComponent);
      
//	    const componentRef = this.zoneContentDirective.viewContainerRef.createComponent(componentFactory);
      
//      (<ZoneComponent> componentRef.instance).data = {};
	}
	
	ngDoCheck() {
		if (this.currentSiZoneContent && this.currentSiZoneContent !== this.siZone.content) {
			this.zoneContentDirective.viewContainerRef.clear();
			this.currentSiZoneContent = null;
		}
		
		if (this.currentSiZoneContent || !this.siZone.content) {
			return;
		}
		
		this.currentSiZoneContent = this.siZone.content;
		this.currentSiZoneContent.initComponent(this.zoneContentDirective.viewContainerRef,
				this.componentFactoryResolver);
	}
	
	get loaded(): boolean {
		return !!this.currentSiZoneContent;
	}

}
