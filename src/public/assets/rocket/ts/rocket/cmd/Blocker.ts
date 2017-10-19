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
				this.observeContext(context)
			}
			
			layer.onNewContext((context: Context) => {
				this.observeContext(context);
				this.check();
			});
		}
		
		private observeContext(context: Context) {
			var checkCallback = () => {
				this.check();
			}
			
			context.on(Context.EventType.SHOW, checkCallback);
			context.on(Context.EventType.HIDE, checkCallback);
			context.on(Context.EventType.CLOSE, checkCallback);
			context.on(Context.EventType.CONTENT_CHANGED, checkCallback);
			context.on(Context.EventType.BLOCKED_CHANGED, checkCallback);
		}
		
		
		init(jqContainer: JQuery) {
			if (this.jqContainer) {
				throw new Error("Blocker already initialized.");
			}
			
			this.jqContainer = jqContainer;
			this.check();
		}
		
		
		private check() {
			if (!this.jqContainer || !this.container.currentLayer.currentContext) return;
			
			if (!this.container.currentLayer.currentContext.locked) {
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