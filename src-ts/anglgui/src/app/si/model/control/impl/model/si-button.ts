
export class SiButton {
	public inputAvailable = false;
	public tooltip: string|null = null;
	public important = false;
	public iconImportant = false;
	public iconAlways = false;
	public labelAlways = false;
	public confirm: SiConfirm|null = null;
	public href: string|null = null;
	public target: string|null = null;

	constructor(public name: string, public btnClass: string, public iconClass: string) {
	}
}

export interface SiConfirm {
	message: string|null;
	okLabel: string|null;
	cancelLabel: string|null;
	danger: boolean;
}
