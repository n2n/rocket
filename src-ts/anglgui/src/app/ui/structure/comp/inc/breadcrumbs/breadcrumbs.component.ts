import { Component, OnInit, Input } from '@angular/core';
import { UiBreadcrumb, UiZone } from '../../../model/ui-zone';
import { SiUiService } from 'src/app/si/manage/si-ui.service';

@Component({
	selector: 'rocket-ui-breadcrumbs',
	templateUrl: './breadcrumbs.component.html',
	styleUrls: ['./breadcrumbs.component.css']
})
export class BreadcrumbsComponent implements OnInit {

	@Input()
	uiZone: UiZone;
	@Input()
	uiBreadcrumbs: UiBreadcrumb[];

	constructor(private siUiService: SiUiService) { }

	ngOnInit() {
	}

	isLast(uiBreadcrumb: UiBreadcrumb): boolean {
		return this.uiBreadcrumbs.length > 0 && this.uiBreadcrumbs[this.uiBreadcrumbs.length - 1] === uiBreadcrumb;
	}
	
	exec(url: string) {
		this.siUiService.navigate(url, this.uiZone.layer);
	}

}
