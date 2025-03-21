import { UiBreadcrumb, UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiLayer } from 'src/app/ui/structure/model/ui-layer';
import { Injector } from '@angular/core';
import { Extractor } from 'src/app/util/mapping/extractor';
import { UiMenuGroup, UiMenuItem } from 'src/app/ui/structure/model/ui-menu';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiEssentialsFactory } from './si-field-essentials-factory';
import { SiGuiFactory } from './si-gui-factory';
import { SiControlFactory } from './si-control-factory';
import { SimpleSiControlBoundary } from '../model/control/impl/model/simple-si-control-boundary';

export class SiUiFactory {

	constructor(private injector: Injector) {
	}

	fillZone(data: any, uiZone: UiZone): void {
		const extr = new Extractor(data);

		const gui = new SiGuiFactory(this.injector).buildGui(extr.reqObject('gui'))!;

		uiZone.title = extr.reqString('title');
		uiZone.breadcrumbs = this.createBreadcrumbs(extr.reqArray('breadcrumbs'), uiZone.layer);
		uiZone.structure = new UiStructure(null, null, gui.createUiStructureModel());

		const controlBoundary = new SimpleSiControlBoundary(gui.getBoundValueBoundaries(), gui.getBoundDeclaration(), uiZone.url);
		uiZone.mainCommandContents = new SiControlFactory(controlBoundary, this.injector)
				.createControls(null, null, extr.reqMap('controls'))
				.map(siControl => siControl.createUiContent(() => uiZone))
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

		const navPoint = SiEssentialsFactory
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

		return new UiMenuItem(extr.reqString('id'), extr.reqString('label'), SiEssentialsFactory
				.createNavPoint(extr.reqObject('navPoint'))
				.toUiNavPoint(this.injector, null));
	}
}


