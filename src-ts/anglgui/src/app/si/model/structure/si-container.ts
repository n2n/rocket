
import { SiLayer, MainSiLayer, PopupSiLayer } from 'src/app/si/model/structure/si-layer';

export class SiContainer {
	private layers: SiLayer[] = [];

	constructor() {
		this.layers.push(new MainSiLayer(this));
	}

	getMainLayer(): MainSiLayer {
		return this.layers[0] as MainSiLayer;
	}

	getPopupLayers(): PopupSiLayer[] {
		return this.layers.slice(1) as PopupSiLayer[];
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
