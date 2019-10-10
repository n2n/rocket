import { Component, OnInit, OnDestroy, ComponentFactoryResolver } from '@angular/core';
import { ActivatedRoute, Router, NavigationStart } from '@angular/router';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { Subscription } from 'rxjs';
import { filter } from 'rxjs/operators';
import { UiContainer } from 'src/app/ui/structure/model/ui-container';
import { MainUiLayer } from 'src/app/ui/structure/model/ui-layer';

@Component({
	selector: 'rocket-ei',
	templateUrl: './ei.component.html',
	styleUrls: ['./ei.component.css']
})
export class EiComponent implements OnInit, OnDestroy {

	private uiContainer: UiContainer;
	private subscription: Subscription;

	constructor(private route: ActivatedRoute, private siUiService: SiUiService,
			private router: Router/*, platformLocation: PlatformLocation*/,
			componentFactoryResolver: ComponentFactoryResolver) {
		this.uiContainer = new UiContainer(componentFactoryResolver);
// 		alert(platformLocation.getBaseHrefFromDOM() + ' ' + route.snapshot.url.join('/'));
	}

	ngOnInit() {
		this.subscription = this.router.events
				.pipe(filter((event) => {
					return (event instanceof NavigationStart);
				}))
				.subscribe((event: NavigationStart) => {
					this.handleNav(event);
				});

		// @todo find out if this works

		let id = 1;
		const curNav = this.router.getCurrentNavigation();
		if (curNav) {
			id = curNav.id;
		}

		const zone = this.mainUiLayer.pushZone(1, this.route.snapshot.url.join('/'));
		this.siUiService.loadZone(zone);

	}

	ngOnDestroy() {
		this.subscription.unsubscribe();
	}

	get mainUiLayer(): MainUiLayer {
		return this.uiContainer.getMainLayer();
	}

	private handleNav(event: NavigationStart) {
		const url = event.url.substr(1);

		switch (event.navigationTrigger) {
		case 'popstate':
			if (event.restoredState &&
					this.mainUiLayer.popZone(event.restoredState.navigationId, url)) {
				break;
			}
		case 'imperative':
			const zone = this.mainUiLayer.pushZone(event.id, url);
			this.siUiService.loadZone(zone);
			break;
		}


	}
}
