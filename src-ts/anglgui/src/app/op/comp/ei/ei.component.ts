import { Component, ComponentFactoryResolver, OnDestroy, OnInit } from '@angular/core';
import { ActivatedRoute, NavigationStart, Router } from '@angular/router';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { Subscription } from 'rxjs';
import { filter } from 'rxjs/operators';
import { UiContainer } from 'src/app/ui/structure/model/ui-container';
import { MainUiLayer } from 'src/app/ui/structure/model/ui-layer';
import { SiModStateService } from 'src/app/si/model/mod/model/si-mod-state.service';
import { Message } from 'src/app/util/i18n/message';

@Component({
	selector: 'rocket-ei',
	templateUrl: './ei.component.html',
	styleUrls: ['./ei.component.css']
})
export class EiComponent implements OnInit, OnDestroy {

	uiContainer: UiContainer;
	private subscription?: Subscription;

	constructor(private route: ActivatedRoute, private siUiService: SiUiService,
			private router: Router/*, platformLocation: PlatformLocation*/,
			componentFactoryResolver: ComponentFactoryResolver,
			private siModState: SiModStateService) {
		this.uiContainer = new UiContainer(componentFactoryResolver);
// 		alert(platformLocation.getBaseHrefFromDOM() + ' ' + route.snapshot.url.join('/'));
	}

	private readonly fallbackId = -1;

	ngOnInit(): void {
		this.subscription = this.router.events
				.pipe(filter((event) => {
					// console.log(event);

					return (event instanceof NavigationStart);
				}))
				.subscribe((event: any) => {
					this.handleNav(event);
				});

		// @todo find out if this works

		let id = this.fallbackId;
		const curNav = this.router.getCurrentNavigation();
		if (curNav) {
			id = curNav.id;
		}

		const zone = this.mainUiLayer.pushRoute(id, this.route.snapshot.url.join('/')).zone;
		this.siUiService.loadZone(zone, false);
	}

	ngOnDestroy(): void {
		this.subscription!.unsubscribe();
		this.subscription = undefined;
	}

	get mainUiLayer(): MainUiLayer {
		return this.uiContainer.getMainLayer();
	}

	private handleNav(event: NavigationStart): void {
		const url = event.url.substr(1);

		switch (event.navigationTrigger) {
		// @ts-ignore
		case 'popstate':
			if (!event.restoredState) {
				break;
			}

			const id = event.restoredState.navigationId;
			if (this.mainUiLayer.containsRouteId(id)) {
				this.mainUiLayer.switchRouteById(id, url);
				break;
			} else if (this.mainUiLayer.containsRouteId(this.fallbackId, url)) {
				this.mainUiLayer.switchRouteById(this.fallbackId, url);
				break;
			} else if (url.length === 0) {
				break;
			}

		case 'imperative':
			this.mainUiLayer.pushRoute(event.id, url);
			this.siUiService.loadZone(this.mainUiLayer.currentRoute!.zone, true);
			break;
		default:
			// console.log('state ' + event.navigationTrigger);
		}

	}

	get messages(): Message[] {
		return this.siModState.lastMessages;
	}
}
