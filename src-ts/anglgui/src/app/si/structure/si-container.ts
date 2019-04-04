
import { SiLayer } from "src/app/si/structure/si-layer";

export class SiContainer {
    
    readonly siLayers: SiLayer[] = [];
    
    constructor() {
    	console.log("huii");
        this.siLayers.push(new SiLayer());
    }
    
    get mainSiLayer(): SiLayer {
    	return this.siLayers[0];
    }
}