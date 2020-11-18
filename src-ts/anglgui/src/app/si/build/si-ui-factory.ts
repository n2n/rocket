import { UiBreadcrumb, UiZoneModel } from 'src/app/ui/structure/model/ui-zone';
import { UiLayer } from 'src/app/ui/structure/model/ui-layer';
import { Injector } from '@angular/core';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiGuiFactory } from './si-gui-factory';
import { UiMenuItem, UiMenuGroup } from 'src/app/ui/structure/model/ui-menu';
import { SiControlFactory } from './si-control-factory';

export class SiUiFactory {

	constructor(private injector: Injector) {
	}

	createZoneModel(data: any, uiLayer: UiLayer|null): UiZoneModel {
		const extr = new Extractor(data);

		const comp = new SiGuiFactory(this.injector).buildGui(extr.reqObject('comp'));

		return {
			title: extr.reqString('title'),
			breadcrumbs: this.createBreadcrumbs(extr.reqArray('breadcrumbs'), uiLayer),
			structureModel: comp.createUiStructureModel(),
			mainCommandContents: [] /*new SiControlFactory(comp, this.injector).createControls(extr.reqArray('controls'))
					.map(siControl => siControl.createUiContent(zone))*/
		};
	}

	createBreadcrumbs(dataArr: Array<any>, uiLayer: UiLayer|null): UiBreadcrumb[] {
		const breadcrumbs: UiBreadcrumb[] = [];

		for (const data of dataArr) {
			breadcrumbs.push(this.createBreadcrumb(data, uiLayer));
		}

		return breadcrumbs;
	}

	createBreadcrumb(data: any, uiLayer: UiLayer|null): UiBreadcrumb {
		const extr = new Extractor(data);

		const navPoint = SiControlFactory
				.createNavPoint(extr.reqObject('navPoint'))
				.toUiNavPoint(this.injector, uiLayer);

		return {
			name: extr.reqString('name'),
			navPoint
		};
	}

	createMenuGroups(dataArr: Array<any>): UiMenuGroup[] {
		const menuGroups = new Array<UiMenuGroup>();
		for (const data of dataArr) {
			menuGroups.push(this.createMenuGroup(data));
		}
		return menuGroups;
	}

	createMenuGroup(data: any): UiMenuGroup {
		const extr = new Extractor(data);

		return new UiMenuGroup(extr.reqString('label'), this.createMenuItems(extr.reqArray('menuItems')));
	}

	createMenuItems(dataArr: Array<any>): UiMenuItem[] {
		return dataArr.map(data => this.createMenuItem(data));
	}

	createMenuItem(data: any): UiMenuItem {
		const extr = new Extractor(data);

		return new UiMenuItem(extr.reqString('id'), extr.reqString('label'), SiControlFactory
				.createNavPoint(extr.reqObject('navPoint'))
				.toUiNavPoint(this.injector, null));
	}
}


