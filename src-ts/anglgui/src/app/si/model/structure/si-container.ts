
import { SiLayer } from "src/app/si/model/structure/si-layer";

export class SiContainer {
    
    readonly layers: SiLayer[] = [];
    
    constructor() {
        this.layers.push(new SiLayer(this, true));
    }
    
    get mainSiLayer(): SiLayer {
    	return this.layers[0];
    }
    
    createLayer(): SiLayer {
    	const layer = new SiLayer(this, false);
    	
    	this.layers.push(layer);
    	layer.onDispose(() => {
    		const i = this.layers.indexOf(layer);
    		if (i > -1) {
    			this.layers.splice(i);
    		}
    	});
    	
    	return layer;
    }
}