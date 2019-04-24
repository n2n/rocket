
import { SiZone } from "src/app/si/model/structure/si-zone";

export class SiLayer {
    private siZones: SiZone[] = [];
	
	constructor(readonly main: boolean) {
	}
	
	pushSiZone(siZone: SiZone) {
		this.siZones.push(siZone);
	}
	
	get curSiZone(): SiZone {
		return this.siZones[this.siZones.length - 1];
	}
}