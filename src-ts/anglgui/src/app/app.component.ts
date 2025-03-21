import { Component, ElementRef, OnInit, Injector, LOCALE_ID } from '@angular/core';
import { TranslationService } from './util/i18n/translation.service';
import { Extractor } from './util/mapping/extractor';
import { UiMenuGroup } from './ui/structure/model/ui-menu';
import { SiUiService } from './si/manage/si-ui.service';
import { AppStateService } from './app-state.service';
import { UserFactory } from './op/user/model/user-factory';
import { User } from './op/user/bo/user';
import { UiNavPoint } from './ui/util/model/ui-nav-point';
import { PlatformService } from './util/nav/platform.service';
import { animate, state, style, transition, trigger } from '@angular/animations';
import { SiBuildTypes } from './si/build/si-build-types';
import {MenuGroupLocalStorage} from './ui/util/model/menu-group-local-storage';
import { SiUiFactory } from './si/build/si-ui-factory';

@Component({
	selector: 'rocket-root',
	templateUrl: './app.component.html',
	styleUrls: ['./app.component.css'],
	animations: [
			    trigger('openClose', [
			state('open', style({opacity: 1, height: '*', 'padding-top': '*', 'padding-bottom': '*'})),
			state('closed', style({opacity: 0, height: 0, 'padding-top': 0, 'padding-bottom': 0, 'overflow': 'hidden'})),
			transition('closed <=> open', animate('100ms'))
		])
	]
})
export class AppComponent implements OnInit {
	title = 'rocket';

	menuGroups!: UiMenuGroup[];

	constructor(private elemRef: ElementRef, private translationService: TranslationService,
			private uiSiService: SiUiService, private appState: AppStateService,
			private platformService: PlatformService, private injector: Injector) {


	}

	ngOnInit(): void {
		const extr = new Extractor(JSON.parse(this.elemRef.nativeElement.getAttribute('data-rocket-angl-data')));

		this.translationService.map = extr.reqStringMap('translationMap');

		this.menuGroups = new SiUiFactory(this.injector).createMenuGroups(extr.reqArray('menuGroups'));
		MenuGroupLocalStorage.toggleOpenStates(this.menuGroups);

		this.appState.user = UserFactory.createUser(extr.reqObject('user'));
		this.appState.pageName = extr.reqString('pageName');

		this.appState.assetsUrl = this.elemRef.nativeElement.getAttribute('data-rocket-assets-url');
		this.appState.localeId = this.elemRef.nativeElement.getAttribute('data-locale-id');
	}

	navRouterLink(navPoint: UiNavPoint): string {
		return navPoint.routerLink || this.platformService.routerUrl(navPoint.href!);
	}

	get user(): User {
		return this.appState.user;
	}

	get pageName(): string {
		return this.appState.pageName;
	}

	get logoSrc(): string {
		return this.appState.assetsUrl + '/img/rocket-logo.svg';
	}
}
