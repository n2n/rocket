import { Component, OnInit } from '@angular/core';
import { LinkOutModel } from '../link-field-model';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { SiUiService } from 'src/app/si/manage/si-ui.service';

@Component({
	selector: 'rocket-link-out-field',
	templateUrl: './link-out-field.component.html',
	styleUrls: ['./link-out-field.component.css']
})
export class LinkOutFieldComponent implements OnInit {

	uiZone: UiZone;
	model: LinkOutModel;

	constructor(private siUiService: SiUiService) {
	}

	callback: () => boolean = () => {
		const navPoint = this.model.getUiNavPoint();
		if (this.uiZone.layer.main || !navPoint.siref) {
			return true;
		}

		this.siUiService.navigateByUrl(navPoint.url, this.uiZone.layer);
	}

	ngOnInit() {
	}
}
