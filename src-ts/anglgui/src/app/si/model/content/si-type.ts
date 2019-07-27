
export class SiType {
	constructor(public id: string, public name: string, public iconClass: string) {
	}
	
	equals(arg: SiType) {
		return arg instanceof SiType && this.id === arg.id;
	}
}