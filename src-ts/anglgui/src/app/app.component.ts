import { Component, ElementRef, OnInit } from '@angular/core';
import { TranslationService } from './util/i18n/translation.service';
import { Extractor } from './util/mapping/extractor';
import { UiFactory } from './ui/build/ui-factory';
import { UiMenuGroup } from './ui/structure/model/ui-menu';
import { SiUiService } from './si/manage/si-ui.service';
import { AppStateService } from './app-state.service';
import { UserFactory } from './op/user/model/user-fatory';
import { User } from './op/user/bo/user';
import { UiNavPoint } from './ui/util/model/ui-nav-point';

@Component({
	selector: 'rocket-root',
	templateUrl: './app.component.html',
	styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
	title = 'rocket';

	menuGroups: UiMenuGroup[];

	constructor(private elemRef: ElementRef, private translationService: TranslationService, private uiSiService: SiUiService,
			private appState: AppStateService) {
	}

	ngOnInit() {
		const extr = new Extractor(JSON.parse(this.elemRef.nativeElement.getAttribute('data-rocket-angl-data')));

		this.translationService.map = extr.reqStringMap('translationMap');
		this.menuGroups = UiFactory.createMenuGroups(extr.reqArray('menuGroups'));
		this.appState.user = UserFactory.createUser(extr.reqObject('user'));
	}

	navRouterLink(navPoint: UiNavPoint): string {
		if (navPoint.siref) {
			return navPoint.url;
		} 

		return this.uiSiService.navRouterUrl(navPoint.url);
	}

	get user(): User {
		return this.appState.user;
	}

}
