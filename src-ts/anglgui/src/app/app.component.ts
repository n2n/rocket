import { Component, ElementRef, OnInit } from '@angular/core';
import { TranslationService } from './util/i18n/translation.service';
import { Extractor } from './util/mapping/extractor';

@Component({
	selector: 'rocket-root',
	templateUrl: './app.component.html',
	styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
	title = 'rocket';

	constructor(private elemRef: ElementRef, private translationService: TranslationService) {
	}

	ngOnInit() {
		const extr = new Extractor(JSON.parse(this.elemRef.nativeElement.getAttribute('data-rocket-angl-data')));
		this.translationService.map = extr.reqStringMap('translationMap');
	}
}
