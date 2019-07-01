import { Component, OnInit, ViewChild, ComponentFactoryResolver } from '@angular/core';
import { Route, ActivatedRoute, Router, UrlSegment, NavigationStart } from "@angular/router";
import { PlatformLocation } from "@angular/common";
import { SiContainer } from "src/app/si/model/structure/si-container";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiCommanderService } from "src/app/si/model/si-commander.service";

@Component({
  selector: 'rocket-ei',
  templateUrl: './ei.component.html',
  styleUrls: ['./ei.component.css']
})
export class EiComponent implements OnInit {

	private siContainer: SiContainer;
	
	constructor(private componentFactoryResolver: ComponentFactoryResolver, private route: ActivatedRoute, 
			private siCommanderService: SiCommanderService, private router: Router/*, platformLocation: PlatformLocation*/) {
		this.siContainer = new SiContainer();
//		alert(platformLocation.getBaseHrefFromDOM() + ' ' + route.snapshot.url.join('/'));
	}

	ngOnInit() {
		this.router.events.subscribe((event) => {
			if (event instanceof NavigationStart) {
				console.log(event);
			}
		});
		this.route.url.subscribe((url: UrlSegment[]) => {
			const zone = this.siContainer.mainSiLayer.pushZone(url.join('/'));
			
			this.siCommanderService.loadZone(zone);
		});
	}
}
