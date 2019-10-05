import { UiZoneModel, UiBreadcrumb } from 'src/app/ui/structure/ui-zone';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiContentFactory } from './si-comp-factory';

export class UiZoneModelFactory {
	createZoneModel(data: any): UiZoneModel {
		const extr = new Extractor(data);

		return {
			title: extr.reqString('title'),
			breadcrumbs: this.createBreadcrumbs(extr.reqArray('breadcrumbs')),
			structureModel: SiContentFactory.createComp(extr.reqObject('comp')),
			controls: []
		};
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

		return {
			url: extr.reqString('url'),
			name: extr.reqString('name')
		};
	}
}
