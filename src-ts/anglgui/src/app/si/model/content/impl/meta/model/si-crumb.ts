export interface SiCrumbGroup {
	crumbs: SiCrumb[];
}

export class SiCrumb {

	constructor(readonly type: SiCrumb.Type, readonly label: string|null, readonly iconClass: string|null) {
	}

	static createIcon(iconClass: string): SiCrumb {
		return new SiCrumb(SiCrumb.Type.ICON, null, iconClass);
	}

	static createLabel(label: string): SiCrumb {
		return new SiCrumb(SiCrumb.Type.LABEL, label, null);
	}
}

export namespace SiCrumb {
	export enum Type {
		LABEL = 'label',
		ICON = 'icon'
	}
}
