namespace rocket.cmd {
	import display = rocket.display;
	import util = rocket.util;
	
	export class Container {
		private jqContainer: JQuery;
		private layers: Array<Layer>;
		
		private jqErrorLayer: JQuery = null;
		
		constructor(jqContainer: JQuery) {
			this.jqContainer = jqContainer;
			this.layers = new Array<Layer>();
			
			var layer = new Layer(this.jqContainer.find(".rocket-main-layer"), this.layers.length, this);
			this.layers.push(layer);
			
			var that = this;
			
			layer.onNewHistoryEntry(function (historyIndex: number, url: Url, context: Context) {
				var stateObj = { 
					"type": "rocketContext",
					"level": layer.getLevel(),
					"url": url,
					"historyIndex": historyIndex
				};
				history.pushState(stateObj, "seite 2", url.toString());
			});
						
			$(window).bind("popstate", function(e) {
				if (that.jqErrorLayer) {
					that.jqErrorLayer.remove();
					that.jqErrorLayer = null;
				}
				
				if (!history.state) {
					layer.go(0, window.location.href);
					return;
				}
				
				if (history.state.type != "rocketContext" || history.state.level != 0) {
					return;
				}
				
				if (!layer.go(history.state.historyIndex, history.state.url)) {
//					history.back();
				}
			});
		}
		
		public handleError(url: string, html: string) {
			var stateObj = { 
				"type": "rocketErrorContext",
				"url": url
			};
			
			if (this.jqErrorLayer) {
                this.jqErrorLayer.remove();
				history.replaceState(stateObj, "n2n Rocket", url);
			} else {
				history.pushState(stateObj, "n2n Rocket", url);
			}
			
			this.jqErrorLayer = $("<div />", { "class": "rocket-error-layer" });
			this.jqErrorLayer.css({ "position": "fixed", "top": 0, "left": 0, "right": 0, "bottom": 0 });
			this.jqContainer.append(this.jqErrorLayer);
			
			var iframe = document.createElement("iframe");
			this.jqErrorLayer.append(iframe);
			
			iframe.contentWindow.document.open();
			iframe.contentWindow.document.write(html);
			iframe.contentWindow.document.close();
			
			$(iframe).css({ "width": "100%", "height": "100%", "background": "white" });
		}
	
		public getMainLayer(): Layer {
			if (this.layers.length > 0) {
				return this.layers[0];
			}
			
			throw new Error("Container empty.");
		}
		
		public getCurrentLayer(): Layer {
			if (this.layers.length == 0) {
				throw new Error("Container empty.");
			}
			
			var layer = null;
			for (var i in this.layers) {
				if (this.layers[i].isVisible()) {
					layer = this.layers[i];
				}
			}
			
			if (layer !== null) return layer;
			
			return this.layers[this.layers.length - 1];
		}
		
		public createLayer(dependentContext: Context = null): Layer {
			var jqLayer = $("<div />", {
				"class": "rocket-layer"
			});
			
			this.jqContainer.append(jqLayer);
			
			var layer = new Layer(jqLayer, this.layers.length, this);
			this.layers.push(layer);
			
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
			
			if (dependentContext === null) {
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
			
			return layer;
		}
			
		public getAllContexts(): Array<Context> {
			var contexts = new Array<Context>();
			
			for (var i in this.layers) {
				var layerContexts = this.layers[i].getContexts(); 
				for (var j in layerContexts) {
					contexts.push(layerContexts[j]);
				}
			}
			
			return contexts;
		}
		
//		public createContext(html: string, newGroup: boolean = false): Context {
////			if (newGroup) {
////				this.currentContentGroup = new ContentGroup();
////				this.additonalContentGroups.push(this.currentContentGroup);
////			}
//			
//			return this.currentLayer.createContext(html, bla);
//		}
	}
}