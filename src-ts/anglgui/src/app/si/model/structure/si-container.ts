
import { SiLayer, MainSiLayer, PopupSiLayer } from "src/app/si/model/structure/si-layer";

export class SiContainer {
    private layers: SiLayer[] = [];
    
    constructor() {
        this.layers.push(new MainSiLayer(this));
    }
    
    getMainLayer(): MainSiLayer {
    	return <MainSiLayer> this.layers[0];
    }
    
    getPopupLayers(): PopupSiLayer[] {
    	return <PopupSiLayer[]> this.layers.slice(1);	
    }
    
    createLayer(): PopupSiLayer {
    	const layer = new PopupSiLayer(this);
    	
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