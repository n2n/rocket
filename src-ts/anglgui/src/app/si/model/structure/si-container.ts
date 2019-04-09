
import { SiLayer } from "src/app/si/model/structure/si-layer";

export class SiContainer {
    
    readonly siLayers: SiLayer[] = [];
    
    constructor() {
        this.siLayers.push(new SiLayer());
    }
    
    get mainSiLayer(): SiLayer {
    	return this.siLayers[0];
    }
}