import { Component, OnInit } from '@angular/core';
import {TranslationService} from '../../../../util/i18n/translation.service';
import {UiBreadcrumb} from '../../../../ui/structure/model/ui-zone';
import {ToolsService} from "../../model/tools.service";

@Component({
	selector: 'rocket-tools',
	templateUrl: './tools.component.html',
	styleUrls: ['./tools.component.css']
})
export class ToolsComponent implements OnInit {
	uiBreadcrumbs: UiBreadcrumb[];

	cacheRecentlyCleared: boolean;

	constructor(translationService: TranslationService, private toolsService: ToolsService) {
		this.uiBreadcrumbs = [
			{
				name: translationService.translate('tool_title'),
				navPoint: {
					routerLink: '/tools'
				}
			}
		];
	}

	public clearCache(): void {
    this.toolsService.clearCache().toPromise().then(() => {
      this.cacheRecentlyCleared = true;
      setTimeout(() => { this.cacheRecentlyCleared = false }, 2000);
    });
  }

	ngOnInit(): void {
	}

}
