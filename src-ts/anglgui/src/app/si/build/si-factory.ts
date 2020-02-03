import { UiZoneModel, UiZone } from 'src/app/ui/structure/model/ui-zone';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiCompEssentialsFactory } from './si-comp-essentials-factory';
import { Injector } from '@angular/core';
import { SiCompFactory } from './si-comp-factory';
import { UiFactory } from './ui-factory';

export class UiZoneModelFactory {
	constructor(private injector: Injector) {
	}

	createZoneModel(data: any, zone: UiZone): UiZoneModel {
		const extr = new Extractor(data);

		const comp = new SiCompFactory(this.injector).createComp(extr.reqObject('comp'));

		return {
			title: extr.reqString('title'),
			breadcrumbs: UiFactory.createBreadcrumbs(extr.reqArray('breadcrumbs')),
			structureModel: comp.createUiStructureModel(),
			mainCommandContents: new SiCompEssentialsFactory(comp, this.injector).createControls(extr.reqArray('controls'))
					.map(siControl => siControl.createUiContent(zone))
		};
	}

}
