import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router, NavigationStart } from '@angular/router';
import { UiContainer } from 'src/app/si/model/structure/si-container';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { Subscription } from 'rxjs';
import { filter } from 'rxjs/operators';
import { MainUiLayer } from 'src/app/si/model/structure/ui-layer';

@Component({
	selector: 'rocket-ei',
	templateUrl: './ei.component.html',
	styleUrls: ['./ei.component.css']
})
export class EiComponent implements OnInit, OnDestroy {

	private siContainer: UiContainer;
	private subscription: Subscription;

	constructor(private route: ActivatedRoute, private siUiService: SiUiService,
			private router: Router/*, platformLocation: PlatformLocation*/) {
		this.siContainer = new UiContainer();
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
		return this.siContainer.getMainLayer();
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
