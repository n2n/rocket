import { Component, ElementRef, OnInit } from '@angular/core';
import { TranslationService } from './util/i18n/translation.service';
import { Extractor } from './util/mapping/extractor';
import { UiFactory } from './ui/build/ui-factory';
import { UiMenuGroup } from './ui/structure/model/ui-menu';

@Component({
	selector: 'rocket-root',
	templateUrl: './app.component.html',
	styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
	title = 'rocket';

	menuGroups: UiMenuGroup[];

	constructor(private elemRef: ElementRef, private translationService: TranslationService) {
	}

	ngOnInit() {
		const extr = new Extractor(JSON.parse(this.elemRef.nativeElement.getAttribute('data-rocket-angl-data')));
		this.translationService.map = extr.reqStringMap('translationMap');

		this.menuGroups = UiFactory.createMenuGroups(UiFactory.createMenuGroups(extr.reqArray('menuGroups')));
	}

}
