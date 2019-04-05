
import { SiZone } from "src/app/si/model/structure/si-zone";

export class SiLayer {
    private siZones: SiZone[] = [];
	
	constructor() {
	}
	
	pushSiZone(siZone: SiZone) {
		this.siZones.push(siZone);
	}
	
	get curSiZone(): SiZone {
		return this.siZones[0];
	}
}