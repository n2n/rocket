import { UiNavPoint } from '../../util/model/ui-nav-point';

export class UiMenuGroup {
	
	isOpen = true;
	
	constructor(public label: string, public menuItems: UiMenuItem[]) {
	}
	
	toggle() {
		console.log(this.isOpen);
		this.isOpen = !this.isOpen;
	}
}

export class UiMenuItem {
	constructor(public id: string, public label: string, public navPoint: UiNavPoint) {
	}
}
