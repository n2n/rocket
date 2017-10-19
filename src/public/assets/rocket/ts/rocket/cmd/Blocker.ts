namespace Rocket.Cmd {
	
	export class Blocker {
		private jqContainer: JQuery;
		private jqBlocker: JQuery = null;
		
		constructor(private container: Container) {
			for (let layer of container.layers) {
				this.observeLayer(layer);
			}
			
			var that = this;
			container.layerOn(Container.LayerEventType.ADDED, function (layer: Layer) {
				that.observeLayer(layer);
				that.check();
			});
			
		}
		
		private observeLayer(layer: Layer) {
			for (let context of layer.contexts) {
				this.observePage(context)
			}
			
			layer.onNewPage((context: Page) => {
				this.observePage(context);
				this.check();
			});
		}
		
		private observePage(context: Page) {
			var checkCallback = () => {
				this.check();
			}
			
			context.on(Page.EventType.SHOW, checkCallback);
			context.on(Page.EventType.HIDE, checkCallback);
			context.on(Page.EventType.CLOSE, checkCallback);
			context.on(Page.EventType.CONTENT_CHANGED, checkCallback);
			context.on(Page.EventType.BLOCKED_CHANGED, checkCallback);
		}
		
		
		init(jqContainer: JQuery) {
			if (this.jqContainer) {
				throw new Error("Blocker already initialized.");
			}
			
			this.jqContainer = jqContainer;
			this.check();
		}
		
		
		private check() {
			if (!this.jqContainer || !this.container.currentLayer.currentPage) return;
			
			if (!this.container.currentLayer.currentPage.locked) {
				if (!this.jqBlocker) return;
				
				this.jqBlocker.remove();
				this.jqBlocker = null;
				return;	
			}
			
			if (this.jqBlocker) return;

			this.jqBlocker = 
					$("<div />", { 
						"class": "rocket-context-block",
						"css": {
							"position": "fixed",
							"top": 0,
							"left": 0,
							"right": 0,
							"bottom": 0
						} 
					})
					.appendTo(this.jqContainer);
		}
	}
	
}