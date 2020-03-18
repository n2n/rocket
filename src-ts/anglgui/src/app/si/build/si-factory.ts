import { UiZoneModel, UiZone } from 'src/app/ui/structure/model/ui-zone';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiControlFactory } from './si-control-factory';
import { Injector } from '@angular/core';
import { SiCompFactory } from './si-comp-factory';
import { UiFactory } from './ui-factory';

export class UiZoneModelFactory {
	constructor(private injector: Injector) {
	}

	createZoneModel(data: any, zone: UiZone): UiZoneModel {
		const extr = new Extractor(data);

		const comp = new SiCompFactory(this.injector).buildComp(extr.reqObject('comp'));

		return {
			title: extr.reqString('title'),
			breadcrumbs: UiFactory.createBreadcrumbs(extr.reqArray('breadcrumbs')),
			structureModel: comp.createUiStructureModel(),
			mainCommandContents: new SiControlFactory(comp, this.injector).createControls(extr.reqArray('controls'))
					.map(siControl => siControl.createUiContent(zone))
		};
	}

}
