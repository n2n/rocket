import { UiBreadcrumb, UiZoneModel } from 'src/app/ui/structure/model/ui-zone';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiContentFactory } from './si-content-factory';

export class UiZoneModelFactory {
	createZoneModel(data: any): UiZoneModel {
		const extr = new Extractor(data);

		return {
			title: extr.reqString('title'),
			breadcrumbs: this.createBreadcrumbs(extr.reqArray('breadcrumbs')),
			structureModel: SiContentFactory.createComp(extr.reqObject('comp')).createUiStructureModel(),
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
