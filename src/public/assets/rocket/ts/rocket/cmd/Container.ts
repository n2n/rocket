namespace Rocket.Cmd {
	import display = Rocket.Display;
	import util = Rocket.util;
	
	export class Container {
		private jqContainer: JQuery;
		private _layers: Array<Layer>;
		private layerCallbackRegistery: util.CallbackRegistry<LayerCallback> = new util.CallbackRegistry<LayerCallback>();
		
		constructor(jqContainer: JQuery) {
			this.jqContainer = jqContainer;
			this._layers = new Array<Layer>();
			
			var layer = new Layer(this.jqContainer.find(".rocket-main-layer"), this._layers.length, this, Jhtml.getOrCreateBrowser().history);
			this._layers.push(layer);
		}

		get layers(): Array<Layer> {
			return this._layers.slice();
		}
		
//		public handleError(url: string, html: string) {
//			var stateObj = { 
//				"type": "rocketErrorContext",
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
		
		public createLayer(dependentContext: Context = null): Layer {
			var jqLayer = $("<div />", {
				"class": "rocket-layer"
			});
			
			this.jqContainer.append(jqLayer);
			
			var layer = new Layer(jqLayer, this._layers.length, this);
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
			
			if (dependentContext === null) {
				this.layerTrigger(Container.LayerEventType.ADDED, layer);
				return layer;
			}
			
			dependentContext.on(Context.EventType.CLOSE, function () {
				layer.close();
			});
			dependentContext.on(Context.EventType.HIDE, function () {
				layer.hide();
			});
			dependentContext.on(Context.EventType.SHOW, function () {
				layer.show();
			});
			
			this.layerTrigger(Container.LayerEventType.ADDED, layer);
			return layer;
		}
			
		public getAllContexts(): Array<Context> {
			var contexts = new Array<Context>();
			
			for (var i in this._layers) {
				var layerContexts = this._layers[i].contexts; 
				for (var j in layerContexts) {
					contexts.push(layerContexts[j]);
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
		
		public layerOff(eventType: Context.EventType, callback: LayerCallback) {
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