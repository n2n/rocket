import { Component, OnInit, Input, ViewChild, ComponentFactoryResolver } from '@angular/core';
import { SiZone } from "src/app/si/model/structure/si-zone";
import { ZoneContentDirective } from "src/app/ui/structure/comp/zone/zone-content.directive";
import { ListZoneContentComponent } from "src/app/ui/content/zone/comp/list-zone-content/list-zone-content.component";

@Component({
  selector: 'rocket-ui-zone',
  templateUrl: './zone.component.html',
  styleUrls: ['./zone.component.css']
})
export class ZoneComponent implements OnInit {

	@Input() siZone: SiZone;
	
    @ViewChild(ZoneContentDirective) zoneContentDirective: ZoneContentDirective;
	
	constructor(private componentFactoryResolver: ComponentFactoryResolver) { }

	ngOnInit() {
		this.siZone.initComponent(this.zoneContentDirective.viewContainerRef,
				this.componentFactoryResolver);
		
//
//		const componentFactory = this.componentFactoryResolver.resolveComponentFactory(ListZoneContentComponent);
//	      
//	      const componentRef = this.zoneContentDirective.viewContainerRef.createComponent(componentFactory);
      
//      (<ZoneComponent> componentRef.instance).data = {};
	}

}
