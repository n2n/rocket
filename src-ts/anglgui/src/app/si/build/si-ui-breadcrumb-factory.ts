import { UiBreadcrumb } from 'src/app/ui/structure/model/ui-zone';
import { UiFactory } from 'src/app/ui/build/ui-factory';
import { SiService } from '../manage/si.service';
import { UiLayer } from 'src/app/ui/structure/model/ui-layer';
import { Injector } from '@angular/core';
import { SiUiService } from '../manage/si-ui.service';
import { Extractor } from 'src/app/util/mapping/extractor';

export class SiUiBreadcrumbFactory {
	constructor(private uiLayer: UiLayer, private injector: Injector) {
	}

	createBreadcrumbs(dataArr: Array<any>): UiBreadcrumb[] {
		const breadcrumbs: UiBreadcrumb[] = [];

		for (const data of dataArr) {
			breadcrumbs.push(this.createBreadcrumb(data));
		}

		return breadcrumbs;
	}

	createBreadcrumb(data: any): UiBreadcrumb {
		const extr = new Extractor(data);

		const navPoint = UiFactory.createNavPoint(extr.reqObject('navPoint'));
		return {
			navPoint,
			name: extr.reqString('name'),
			callback: () => {
				if (!navPoint.siref || this.uiLayer.main) {
					return true;
				}

				this.injector.get(SiUiService).navigateByUrl(navPoint.url, this.uiLayer);
				return false;
			}
		};
	}
}
