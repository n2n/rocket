
import { SiZone } from "src/app/si/model/structure/si-zone";

export class SiLayer {
    private zoneMap = new Map<string, SiZone>();
	private zones: Array<SiZone> = [];
    
	constructor(readonly main: boolean) {
	}
	
	pushZone(url: string): SiZone {
		let zone = this.zoneMap.get(url)
		
		if (!zone) {
			zone = new SiZone(url, this);
			this.zones.push(zone);
		}
		
		return zone;
	}
	
	get curSiZone(): SiZone {
		return this.zones[this.zones.length - 1];
	}
}