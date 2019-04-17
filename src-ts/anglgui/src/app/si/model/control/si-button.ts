
export class SiButton {
	public tooltip: string|null = null;
	public important = false;
	public iconImportant = false;
	public labelImportant = false;
	public confirm: SiConfirm|null = null;
	
	constructor(public name: string, public btnClass: string, public iconClass: string) {
		
	}
}

export interface SiConfirm {
	message: string|null;
	okLabel: string|null;
	cancelLabel: string|null;
}