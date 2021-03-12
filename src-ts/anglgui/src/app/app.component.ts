import { Component, ElementRef, OnInit, Injector, LOCALE_ID } from '@angular/core';
import { TranslationService } from './util/i18n/translation.service';
import { Extractor } from './util/mapping/extractor';
import { UiMenuGroup } from './ui/structure/model/ui-menu';
import { SiUiService } from './si/manage/si-ui.service';
import { AppStateService } from './app-state.service';
import { UserFactory } from './op/user/model/user-fatory';
import { User } from './op/user/bo/user';
import { UiNavPoint } from './ui/util/model/ui-nav-point';
import { PlatformService } from './util/nav/platform.service';
import { SiUiFactory } from './si/build/si-ui-factory';
import {animate, state, style, transition, trigger} from '@angular/animations';

@Component({
	selector: 'rocket-root',
	templateUrl: './app.component.html',
	styleUrls: ['./app.component.css'],
	animations: [
	    trigger('openClose', [
		  state('true', style({opacity: 1, height: '*', overflow: 'hidden'})),
		  state('false', style({opacity: 0, height: 0, padding: 0})),
		  transition('false <=> true', animate('500ms'))
		])
	]
})
export class AppComponent implements OnInit {
	title = 'rocket';

	menuGroups: UiMenuGroup[];

	constructor(private elemRef: ElementRef, private translationService: TranslationService,
			private uiSiService: SiUiService, private appState: AppStateService,
			private platformService: PlatformService, private injector: Injector) {
	}

	ngOnInit() {
		const extr = new Extractor(JSON.parse(this.elemRef.nativeElement.getAttribute('data-rocket-angl-data')));
		
		this.translationService.map = extr.reqStringMap('translationMap');
		this.menuGroups = new SiUiFactory(this.injector).createMenuGroups(extr.reqArray('menuGroups'));
		this.appState.user = UserFactory.createUser(extr.reqObject('user'));
		this.appState.pageName = extr.reqString('pageName');

		this.appState.assetsUrl = this.elemRef.nativeElement.getAttribute('data-rocket-assets-url');
		this.appState.localeId = this.elemRef.nativeElement.getAttribute('data-locale-id');
	}

	navRouterLink(navPoint: UiNavPoint): string {
		return navPoint.routerLink || this.platformService.routerUrl(navPoint.href);
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
