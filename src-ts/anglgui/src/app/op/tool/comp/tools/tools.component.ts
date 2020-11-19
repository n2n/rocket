import { Component, OnInit } from '@angular/core';
import {TranslationService} from '../../../../util/i18n/translation.service';
import {UiBreadcrumb} from '../../../../ui/structure/model/ui-zone';

@Component({
	selector: 'rocket-tools',
	templateUrl: './tools.component.html',
	styleUrls: ['./tools.component.css']
})
export class ToolsComponent implements OnInit {
	uiBreadcrumbs: UiBreadcrumb[];

	constructor(translationService: TranslationService) {
		this.uiBreadcrumbs = [
			{
				name: translationService.translate('tool_title'),
				navPoint: {
					routerLink: '/tools'
				}
			}
		];
	}

	ngOnInit(): void {
	}

}
