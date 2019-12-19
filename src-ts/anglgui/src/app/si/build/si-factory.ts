import { UiBreadcrumb, UiZoneModel } from 'src/app/ui/structure/model/ui-zone';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiCompEssentialsFactory } from './si-comp-essentials-factory';
import { Injector } from '@angular/core';
import { SiCompFactory } from './si-comp-factory';

export class UiZoneModelFactory {
	constructor(private injector: Injector) {
	}

	createZoneModel(data: any): UiZoneModel {
		const extr = new Extractor(data);

		const comp = new SiCompFactory(this.injector).createComp(extr.reqObject('comp'));

		return {
			title: extr.reqString('title'),
			breadcrumbs: this.createBreadcrumbs(extr.reqArray('breadcrumbs')),
			structureModel: comp.createUiStructureModel(),
			mainCommandContents: new SiCompEssentialsFactory(comp).createControls(extr.reqArray('controls'))
					.map(siControl => siControl.createUiContent())
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
