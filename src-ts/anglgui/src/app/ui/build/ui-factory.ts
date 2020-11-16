import { Extractor } from 'src/app/util/mapping/extractor';
import { UiBreadcrumb } from 'src/app/ui/structure/model/ui-zone';
import { UiNavPoint } from 'src/app/ui/util/model/ui-nav-point';
import { UiMenuGroup, UiMenuItem } from 'src/app/ui/structure/model/ui-menu';

export class UiFactory {

	

	static createMenuGroups(dataArr: Array<any>): UiMenuGroup[] {
		const menuGroups = new Array<UiMenuGroup>();
		for (const data of dataArr) {
			menuGroups.push(this.createMenuGroup(data));
		}
		return menuGroups;
	}

	static createMenuGroup(data: any): UiMenuGroup {
		const extr = new Extractor(data);

		return new UiMenuGroup(extr.reqString('label'), UiFactory.createMenuItems(extr.reqArray('menuItems')));
	}

	static createMenuItems(dataArr: Array<any>): UiMenuItem[] {
		return dataArr.map(data => this.createMenuItem(data));
	}

	static createMenuItem(data: any): UiMenuItem {
		const extr = new Extractor(data);

		return new UiMenuItem(extr.reqString('id'), extr.reqString('label'),
				UiFactory.createNavPoint(extr.reqObject('navPoint')));
	}

	static createNavPoint(data: any): UiNavPoint {
		const extr = new Extractor(data);

		return {
			url: extr.reqString('url'),
			siref: extr.reqBoolean('siref')
		};
	}
}
