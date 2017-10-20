namespace Rocket.Cmd {
	import display = Rocket.Display;
	
	export class Container implements Jhtml.CompHandler {
		private jqContainer: JQuery;
		private _layers: Array<Layer>;
		private layerCallbackRegistery: Rocket.util.CallbackRegistry<LayerCallback> = new Rocket.util.CallbackRegistry<LayerCallback>();
		
		constructor(jqContainer: JQuery) {
			this.jqContainer = jqContainer;
			this._layers = new Array<Layer>();
			
			var layer = new Layer(this.jqContainer.find(".rocket-main-layer"), this._layers.length, this, 
					Jhtml.getOrCreateMonitor());
			this._layers.push(layer);
			
			Jhtml.getOrCreateContext().registerCompHandler("rocket-page", this);
		}
		
		attachComp(comp: Jhtml.Comp): boolean {
			//alert("comp comp comp!!");
			return false;
		}
		
		detachComp(comp: Jhtml.Comp): boolean {
			return false;
		}
		
		replaceComp(oldComp: Jhtml.Comp, newComp: Jhtml.Comp): boolean {
			return false;
		}

		get layers(): Array<Layer> {
			return this._layers.slice();
		}
		
//		public handleError(url: string, html: string) {
//			var stateObj = { 
//				"type": "rocketErrorPage",
//				"url": url
//			};
//			
//			if (this.jqErrorLayer) {
//                this.jqErrorLayer.remove();
//				history.replaceState(stateObj, "n2n Rocket", url);
//			} else {
//				history.pushState(stateObj, "n2n Rocket", url);
//			}
//			
//			this.jqErrorLayer = $("<div />", { "class": "rocket-error-layer" });
//			this.jqErrorLayer.css({ "position": "fixed", "top": 0, "left": 0, "right": 0, "bottom": 0 });
//			this.jqContainer.append(this.jqErrorLayer);
//			
//			var iframe = document.createElement("iframe");
//			this.jqErrorLayer.append(iframe);
//			
//			iframe.contentWindow.document.open();
//			iframe.contentWindow.document.write(html);
//			iframe.contentWindow.document.close();
//			
//			$(iframe).css({ "width": "100%", "height": "100%", "background": "white" });
//		}
	
		get mainLayer(): Layer {
			if (this._layers.length > 0) {
				return this._layers[0];
			}
			
			throw new Error("Container empty.");
		}
		
		get currentLayer(): Layer {
			if (this._layers.length == 0) {
				throw new Error("Container empty.");
			}
			
			var layer = null;
			for (let i in this._layers) {
				if (this._layers[i].visible) {
					layer = this._layers[i];
				}
			}
			
			if (layer !== null) return layer;
			
			return this._layers[this._layers.length - 1];
		}
		
		private unregisterLayer(layer: Layer) {
			var i = this._layers.indexOf(layer);
			if (i < 0) return;
			
			this._layers.splice(i, 1);
			
			this.layerTrigger(Container.LayerEventType.REMOVED, layer);
		}
		
		public createLayer(dependentPage: Page = null): Layer {
			var jqLayer = $("<div />", {
				"class": "rocket-layer"
			});
			
			this.jqContainer.append(jqLayer);
			
			var layer = new Layer(jqLayer, this._layers.length, this, 
					Jhtml.Monitor.from(jqLayer.get(0)));
			this._layers.push(layer);
			
			var jqToolbar = $("<div />", {
				"class": "rocket-layer-toolbar rocket-simple-commands"
			});
			jqLayer.append(jqToolbar);
			
			var jqButton = $("<button />", { 
				"class": "btn btn-danger"
			}).append($("<i />", {
				"class": "fa fa-times"
			})).click(function () {
				layer.close();
			});
			jqToolbar.append(jqButton);
			
			var that = this;
			layer.on(Layer.EventType.CLOSE, function () {
				that.unregisterLayer(layer);
			})
			
			if (dependentPage === null) {
				this.layerTrigger(Container.LayerEventType.ADDED, layer);
				return layer;
			}
			
			dependentPage.on(Page.EventType.CLOSE, function () {
				layer.close();
			});
			dependentPage.on(Page.EventType.HIDE, function () {
				layer.hide();
			});
			dependentPage.on(Page.EventType.SHOW, function () {
				layer.show();
			});
			
			this.layerTrigger(Container.LayerEventType.ADDED, layer);
			return layer;
		}
			
		public getAllPages(): Array<Page> {
			var contexts = new Array<Page>();
			
			for (var i in this._layers) {
				var layerPages = this._layers[i].contexts; 
				for (var j in layerPages) {
					contexts.push(layerPages[j]);
				}
			}
			
			return contexts;
		}
		
		private layerTrigger(eventType: Container.LayerEventType, layer: Layer) {
			var container = this;
			this.layerCallbackRegistery.filter(eventType.toString())
					.forEach(function (callback: LayerCallback) {
						callback(layer);
					});
		}
		
		public layerOn(eventType: Container.LayerEventType, callback: LayerCallback) {
			this.layerCallbackRegistery.register(eventType.toString(), callback);
		}
		
		public layerOff(eventType: Page.EventType, callback: LayerCallback) {
			this.layerCallbackRegistery.unregister(eventType.toString(), callback);
		}
	}
	
	
	export namespace Container {
		export enum LayerEventType {
			REMOVED /*= "removed"*/,
			ADDED /*= "added"*/
		}
	}
}