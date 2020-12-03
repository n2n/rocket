import { Component, OnInit } from '@angular/core';
import {TranslationService} from '../../../../util/i18n/translation.service';
import {UiBreadcrumb} from '../../../../ui/structure/model/ui-zone';
import {ToolsService} from "../../model/tools.service";
import {UiToast} from "../../../../ui/structure/model/ui-toast";
import {Message, MessageSeverity} from "../../../../util/i18n/message";

@Component({
	selector: 'rocket-tools',
	templateUrl: './tools.component.html',
	styleUrls: ['./tools.component.css']
})
export class ToolsComponent implements OnInit {
	uiBreadcrumbs: UiBreadcrumb[];

	cacheRecentlyCleared: boolean;
  toasts: UiToast[] = [];

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
      this.toasts.push(new UiToast(new Message("tools_cache_cleared_info", false,
          MessageSeverity.SUCCESS), 2000));
    });
  }

	ngOnInit(): void {
	}
}
