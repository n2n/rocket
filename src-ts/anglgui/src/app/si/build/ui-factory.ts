import { Extractor } from 'src/app/util/mapping/extractor';
import { UiBreadcrumb } from 'src/app/ui/structure/model/ui-zone';
import { UiNavPoint } from 'src/app/ui/util/model/ui-nav-point';

export class UiFactory {

	static createBreadcrumbs(dataArr: Array<any>): UiBreadcrumb[] {
		const breadcrumbs: UiBreadcrumb[] = [];

		for (const data of dataArr) {
			breadcrumbs.push(this.createBreadcrumb(data));
		}

		return breadcrumbs;
	}

	static createBreadcrumb(data: any): UiBreadcrumb {
		const extr = new Extractor(data);

		return {
			navPoint: UiFactory.createNavPoint(extr.reqObject('navPoint')),
			name: extr.reqString('name')
		};
	}

	static createNavPoint(data: any): UiNavPoint {
		const extr = new Extractor(data);

		return {
			url: extr.reqString('url'),
			siref: extr.reqBoolean('siref')
		};
	}
}
