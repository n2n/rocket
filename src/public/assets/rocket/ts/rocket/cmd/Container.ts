namespace rocket.cmd {
	
	export class Container {
		private jqContainer: JQuery;
		private layers: Array<Layer>;
		private counter: number = 0;
		
		constructor(jqContainer: JQuery) {
			this.jqContainer = jqContainer;
			this.layers = new Array<Layer>();
			
			var layer = new Layer(this.jqContainer.find(".rocket-main-layer"), this.layers.length);
			this.layers.push(layer);
			
			var that = this;
			
			layer.onNewContext(function () {
				that.updateUrl();
			});
			
			$(window).bind("popstate", function(e) {
				if (history.state.type != "rocketContext"
						|| history.state.level != 1) {
					that.getMainLayer().exec(history.state.url);
					return;
				}
			});
		}
		
		public getMainLayer(): Layer {
			if (this.layers.length > 0) {
				return this.layers[0];
			}
			
			throw new Error("MainLayer ");
		}
			
		
		private updateUrl() {
			var mainLayer = this.getMainLayer();
			var stateObj = { 
				"type": "rocketContext",
				"level": mainLayer.getLevel(),
				"contextUrl": mainLayer.getActiveContext().getUrl()
			};
			history.pushState(stateObj, "seite 2", this.getMainLayer().getCurrentContext().getUrl());
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
	
	export class Layer {
		private jqContentGroup: JQuery;
		private level: number;
		private contexts: Array<Context>;
		private activeContextIndex: number;
		private onNewContextCallbacks: Array<OnNewContextCallback>;
		
		constructor(jqContentGroup: JQuery, level: number) {
			this.contexts = new Array<Context>();
			this.onNewContextCallbacks = new Array<OnNewContextCallback>();
			this.jqContentGroup = jqContentGroup;
			this.level = level;
		}
		
		public getLevel() {
			return this.level;
		}
		
		public getActiveContext(): Context {
			if (this.contexts.length == 0) {
				throw new Error("no context avaialble");
			}
			
			return this.contexts[this.activeContextIndex];
		} 
		
		public exec(url: string, config: ExecConfig = null) {
			var forceReload = false;
			var showLoadingContext = true;
			var doneCallback;
			
			if (config !== null) {
				forceReload = config.forceReload === true;
				showLoadingContext = config.showLoadingContext !== false
				doneCallback = config.done;
			}
			
			var context = null;
			
			for (var i in this.contexts) {
				if (url != this.contexts[i].getUrl()) continue;
					
				context = this.contexts[i];
				context.show();
				
				if (forceReload) break;
					 
				if (doneCallback) {
					setTimeout(function () { doneCallback(new ExecResult(null, context)); }, 0);
					return;
				}
			}
			
			if (context === null && showLoadingContext) {
				context = this.createContext(url);
			}
			
			context.clear(true);
		
			var that = this;
			$.ajax({
				"url": url,
				"dataType": "json"
			}).fail(function (data) {
				alert(data);
			}).done(function (data) {
				context.applyHtml(n2n.ajah.analyze(data));
				n2n.ajah.update();
				
				if (doneCallback) {
					doneCallback(new ExecResult(null, context));
				}
			});
		}
		
		private createContext(url: string): Context {
			var jqContent = $("<div/>");
			this.jqContentGroup.append(jqContent);
			var content = new Context(jqContent, url);
			
			this.activeContextIndex = this.contexts.length;
			this.contexts.push(content);
			
			for (var i in this.onNewContextCallbacks) {
				this.onNewContextCallbacks[i](content);
			}
			
			return content;
		}
		
		public clear() {
			for (var i in this.contexts) {
				this.contexts[i].dispose();
			}
		}
		
		public dispose() {
			this.contexts = new Array<Context>();
			this.jqContentGroup.remove();
		}
		
		public getCurrentContext(): Context {
			if (this.contexts.length == 0) {
				throw new Error("no context available");
			}
			
			return this.contexts[this.contexts.length - 1];
		}
		
		public onNewContext(onNewContextCallback: OnNewContextCallback) {
			this.onNewContextCallbacks.push(onNewContextCallback);
		}
	}
	
	interface ExecConfig {
		forceReload: boolean 
		showLoadingContext: boolean; 
		done: (ExecResult) => any;
	}
	
	class ExecResult {
		constructor(order, context: Context) {
		}
	}
	
	interface OnNewContextCallback {
		(context: Context): any
	}
	
	export class Context {
		private jqContent: JQuery;
		private url: string;
		
		constructor(jqContent: JQuery, url: string) {
			this.jqContent = jqContent;
			this.url = url;
			jqContent.addClass("rocket-context");
			jqContent.data("rocketContent", this);
		}
		
		public getUrl(): string {
			return this.url;
		}
		
//		public hide() {
//			this.jqContent.hide();	
//		}
		
		public dispose() {
			this.jqContent.remove();
		}
		
		public show() {
			this.jqContent.show();
		}
		
		public hide() {
			this.jqContent.hide();
		}
		
		public clear(loading: boolean = false) {
			this.jqContent.empty();
			this.jqContent.addClass("rocket-loading");
		}
			
		public applyHtml(html: string) {
			this.jqContent.removeClass("rocket-loading");
			this.jqContent.html(html);
		} 
		
		public static findFrom(jqElem: JQuery) {
			if (!jqElem.hasClass(".rocket-context")) {
				jqElem = jqElem.parents(".rocket-context");
			}
			
			var content = jqElem.data("rocketContext");
			alert(typeof content);
		}
		
	}
	
	export class Entry {
		
	}
}