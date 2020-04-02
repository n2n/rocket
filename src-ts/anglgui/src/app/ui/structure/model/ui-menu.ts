import { UiNavPoint } from '../../util/model/ui-nav-point';

export class UiMenuGroup {
	constructor(public label: string, public menuItems: UiMenuItem[]) {
	}
}

export class UiMenuItem {
	constructor(public id: string, public label: string, public navPoint: UiNavPoint) {
	}
}
