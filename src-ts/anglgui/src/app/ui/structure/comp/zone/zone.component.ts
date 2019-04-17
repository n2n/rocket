import { Component, OnInit, DoCheck, Input, ViewChild, ComponentFactoryResolver } from '@angular/core';
import { SiZone } from "src/app/si/model/structure/si-zone";
import { ZoneContentDirective } from "src/app/ui/structure/comp/zone/zone-content.directive";
import { ListZoneContentComponent } from "src/app/ui/content/zone/comp/list-zone-content/list-zone-content.component";

@Component({
  selector: 'rocket-ui-zone',
  templateUrl: './zone.component.html',
  styleUrls: ['./zone.component.css']
})
export class ZoneComponent implements OnInit, DoCheck {

	@Input() siZone: SiZone;
	
    @ViewChild(ZoneContentDirective) zoneContentDirective: ZoneContentDirective;
	
    private init = false;
    
    constructor(private componentFactoryResolver: ComponentFactoryResolver) { }

	ngOnInit() {
		
//		const componentFactory = this.componentFactoryResolver.resolveComponentFactory(ListZoneContentComponent);
//	      
//	      const componentRef = this.zoneContentDirective.viewContainerRef.createComponent(componentFactory);
      
//      (<ZoneComponent> componentRef.instance).data = {};
	}
	
	ngDoCheck() {
		if (this.init || !this.siZone.hasContent()) {
			return;
		}
		
		this.init = true;
		this.siZone.content.initComponent(this.zoneContentDirective.viewContainerRef,
				this.componentFactoryResolver);
	}
	
	get loaded(): boolean {
		return this.siZone.hasContent();
	}

}
