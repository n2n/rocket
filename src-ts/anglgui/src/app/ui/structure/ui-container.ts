
import { UiLayer, MainUiLayer, PopupUiLayer } from 'src/app/si/model/structure/ui-layer';

export class UiContainer {
	private layers: UiLayer[] = [];

	constructor() {
		this.layers.push(new MainUiLayer(this));
	}

	getMainLayer(): MainUiLayer {
		return this.layers[0] as MainUiLayer;
	}

	getPopupLayers(): PopupUiLayer[] {
		return this.layers.slice(1) as PopupUiLayer[];
	}

	createLayer(): PopupUiLayer {
		const layer = new PopupUiLayer(this);

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
