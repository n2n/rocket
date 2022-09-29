import { UiNavPoint } from '../../util/model/ui-nav-point';
import {MenuGroupLocalStorage} from '../../util/model/menu-group-local-storage';

export class UiMenuGroup {

	id;
	isOpen = true;

	constructor(public label: string, public menuItems: UiMenuItem[]) {
		this.id = label + this.menuItems.map(mi => mi.id).join(',');
	}

	toggle() {
		this.isOpen = !this.isOpen;
		MenuGroupLocalStorage.saveOpenState(this, this.isOpen);
	}
}

export class UiMenuItem {
	constructor(public id: string, public label: string, public navPoint: UiNavPoint) {
	}
}

