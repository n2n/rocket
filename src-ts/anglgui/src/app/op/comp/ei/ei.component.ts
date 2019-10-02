import { Component, OnInit, ViewChild, ComponentFactoryResolver, OnDestroy } from '@angular/core';
import { Route, ActivatedRoute, Router, UrlSegment, NavigationStart } from "@angular/router";
import { PlatformLocation } from "@angular/common";
import { SiContainer } from "src/app/si/model/structure/si-container";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiCommanderService } from "src/app/si/model/si-commander.service";
import { Subscription } from "rxjs";
import { filter } from "rxjs/operators";
import { MainSiLayer, PopupSiLayer } from "src/app/si/model/structure/si-layer";

@Component({
	selector: 'rocket-ei',
	templateUrl: './ei.component.html',
	styleUrls: ['./ei.component.css']
})
export class EiComponent implements OnInit, OnDestroy {

	private siContainer: SiContainer;
	private subscription: Subscription;

	constructor(private componentFactoryResolver: ComponentFactoryResolver, private route: ActivatedRoute, 
			private siCommanderService: SiCommanderService, private router: Router/*, platformLocation: PlatformLocation*/) {
		this.siContainer = new SiContainer();
//		alert(platformLocation.getBaseHrefFromDOM() + ' ' + route.snapshot.url.join('/'));
	}

	ngOnInit() {
		this.subscription = this.router.events
				.pipe(filter((event) => { 
					return (event instanceof NavigationStart); 
				}))
				.subscribe((event: NavigationStart) => {
					this.handleNav(event)
				});
		
		// @todo find out if this works
		
		let id = 1;
		const curNav = this.router.getCurrentNavigation()
		if (curNav) {
			id = curNav.id;
		}
		
		const zone = this.mainSiLayer.pushZone(1, this.route.snapshot.url.join('/'));
		this.siCommanderService.loadZone(zone);
		
	}
	
	ngOnDestroy() {
		this.subscription.unsubscribe();
	}
	
	get mainSiLayer(): MainSiLayer {
		return this.siContainer.getMainLayer();
	}
	
	private handleNav(event: NavigationStart) {
		const url = event.url.substr(1);
		
		switch (event.navigationTrigger) {
		case 'popstate':
			if (event.restoredState && 
					this.mainSiLayer.popZone(event.restoredState.navigationId, url)) {
				break;
			}
		case 'imperative':
			const zone = this.mainSiLayer.pushZone(event.id, url);
			this.siCommanderService.loadZone(zone);
			break;
		}
		
		
	}
}
