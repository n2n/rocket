import { UiZoneModel, UiZone } from 'src/app/ui/structure/model/ui-zone';
import { Extractor } from 'src/app/util/mapping/extractor';
import { Injector } from '@angular/core';
import { SiGuiFactory } from './si-gui-factory';
import { SiUiBreadcrumbFactory } from './si-ui-breadcrumb-factory';

export class UiZoneModelFactory {
	constructor(private injector: Injector) {
	}

	createZoneModel(data: any, zone: UiZone): UiZoneModel {
		const extr = new Extractor(data);

		const comp = new SiGuiFactory(this.injector).buildGui(extr.reqObject('comp'));

		return {
			title: extr.reqString('title'),
			breadcrumbs: new SiUiBreadcrumbFactory(zone.layer, this.injector).createBreadcrumbs(extr.reqArray('breadcrumbs')),
			structureModel: comp.createUiStructureModel(),
			mainCommandContents: [] /*new SiControlFactory(comp, this.injector).createControls(extr.reqArray('controls'))
					.map(siControl => siControl.createUiContent(zone))*/
		};
	}

}
