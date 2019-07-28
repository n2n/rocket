
export class SiType {
	constructor(public typeId: string, public name: string, public iconClass: string) {
	}
	
	equals(arg: SiType) {
		return arg instanceof SiType && this.typeId === arg.typeId;
	}
}