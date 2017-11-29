namespace Rocket.Cmd {
	import display = Rocket.Display;
	
	export class Container {
		private jqContainer: JQuery;
		private _layers: Array<Layer>;
		private layerCallbackRegistery: Rocket.util.CallbackRegistry<LayerCallback> = new Rocket.util.CallbackRegistry<LayerCallback>();
		
		constructor(jqContainer: JQuery) {
			this.jqContainer = jqContainer;
			this._layers = new Array<Layer>();
			
			var layer = new Layer(this.jqContainer.find(".rocket-main-layer"), this._layers.length, this, 
					Jhtml.getOrCreateMonitor());
			this.registerLayer(layer);
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
		
		private registerLayer(layer: Layer) {
			layer.monitor.onDirective((evt) => this.directiveExecuted(evt.directive));
			this._layers.push(layer);
		}
		
		private directiveExecuted(directive: Jhtml.Directive) {
			let data = directive.getAdditionalData();

			if (!data || !data.rocketEvent || !data.rocketEvent.eiMods) return;
			
			let zoneClearer = new ZoneClearer(this.getAllZones());
			
			let eiMods = data.rocketEvent.eiMods;
			for (let supremeEiTypeId in eiMods) {
				if (!eiMods[supremeEiTypeId].idReps && eiMods[supremeEiTypeId].draftIds) {
					zoneClearer.clearBySupremeEiType(supremeEiTypeId);
					continue;
				}
				
				if (eiMods[supremeEiTypeId].idReps) {
					for (let idRep in eiMods[supremeEiTypeId].idReps) {
						let mode = eiMods[supremeEiTypeId].idReps[idRep];
						zoneClearer.clearByIdRep(supremeEiTypeId, idRep, mode == "removed");
					}
				}
				
				if (eiMods[supremeEiTypeId].draftIds) {
					for (let draftId in eiMods[supremeEiTypeId].draftIds) {
						let mode = eiMods[supremeEiTypeId].draftIds[draftId];
						zoneClearer.clearByDraftId(supremeEiTypeId, parseInt(draftId), mode == "removed");
					}
				}
			}
		}
		
		public createLayer(dependentPage: Zone = null): Layer {
			var jqLayer = $("<div />", {
				"class": "rocket-layer"
			});
			
			this.jqContainer.append(jqLayer);
			
			var layer = new Layer(jqLayer, this._layers.length, this, 
					Jhtml.Monitor.create(jqLayer.get(0), new Jhtml.History()));
			this.registerLayer(layer);
			
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
			
			let reopenable = false;
			dependentPage.on(Zone.EventType.CLOSE, function () {
				layer.close();
			});
			dependentPage.on(Zone.EventType.CONTENT_CHANGED, function () {
				layer.close();
			});
			dependentPage.on(Zone.EventType.HIDE, function () {
				reopenable = layer.visible;
				layer.hide();
			});
			dependentPage.on(Zone.EventType.SHOW, function () {
				if (!reopenable) return;
				
				layer.show();
			});
			
			this.layerTrigger(Container.LayerEventType.ADDED, layer);
			return layer;
		}
			
		public getAllZones(): Array<Zone> {
			var contexts = new Array<Zone>();
			
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
		
		public layerOff(eventType: Zone.EventType, callback: LayerCallback) {
			this.layerCallbackRegistery.unregister(eventType.toString(), callback);
		}
	}
	
	
	class ZoneClearer {
		constructor(private zones: Zone[]) {
			
		}
		
		clearBySupremeEiType(supremeEiTypeId: string) {
			for (let zone of this.zones) {
				if (!zone.page || zone.page.config.frozen || zone.page.disposed) {
					continue;
				}
				
				if (Display.Entry.hasSupremeEiTypeId(zone.jQuery, supremeEiTypeId)) {
					zone.page.dispose();
				}
			}
		}
		
		clearByIdRep(supremeEiTypeId: string, idRep: string, remove: boolean) {
			for (let zone of this.zones) {
				if (!zone.page || zone.page.disposed) continue;

				if (remove && this.removeByIdRep(zone, supremeEiTypeId, idRep)) {
					continue;
				}
				
				if (zone.page.config.frozen) continue;
				
				if (Display.Entry.hasIdRep(zone.jQuery, supremeEiTypeId, idRep)) {
					zone.page.dispose();
				}
			}
		}
		
		private removeByIdRep(zone: Zone, supremeEiTypeId: string, idRep: string): boolean {
			let entries = Display.Entry.findByIdRep(zone.jQuery, supremeEiTypeId, idRep);
			if (entries.length == 0) return true;
			
			let success = true;
			for (let entry of entries) {
				if (entry.collection) {
					entry.dispose();
				} else {
					success = false;
				}
			}
			return success;
		}
		
		clearByDraftId(supremeEiTypeId: string, draftId: number, remove: boolean) {
			for (let zone of this.zones) {
				if (!zone.page || zone.page.disposed) continue;
				
				if (remove && this.removeByDraftId(zone, supremeEiTypeId, draftId)) {
					continue;
				}
				
				if (zone.page.config.frozen) continue;
				
				if (Display.Entry.hasDraftId(zone.jQuery, supremeEiTypeId, draftId)) {
					zone.page.dispose();
				}
			}
		}
		
		private removeByDraftId(zone: Zone, supremeEiTypeId: string, draftId: number): boolean {
			let entries = Display.Entry.findByDraftId(zone.jQuery, supremeEiTypeId, draftId);
			if (entries.length == 0) return true;
			
			let success = true;
			for (let entry of entries) {
				if (entry.collection) {
					entry.dispose();
				} else {
					success = false;
				}
			}
			return success;
		}
	}
	
	export namespace Container {
		export enum LayerEventType {
			REMOVED /*= "removed"*/,
			ADDED /*= "added"*/
		}
	}
}