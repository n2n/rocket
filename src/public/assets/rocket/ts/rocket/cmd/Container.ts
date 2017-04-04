namespace rocket.cmd {
	
	export class Container {
		private jqContainer: JQuery;
		private mainLayer: Layer;
		private additonalLayers: Array<Layer>
		private currentLayer: Layer;
		
		constructor(jqContainer: JQuery) {
			this.jqContainer = jqContainer;
			
			this.mainLayer = new Layer(this.jqContainer.find(".rocket-main-layer"));
			
			var that = this;
			this.mainLayer.onNewContext(function () {
				that.updateUrl();
			});
		}
		
		private updateUrl() {
			console.log(this.mainLayer.getCurrentContext().getUrl());
			var stateObj = { foo: "bar" };
			history.pushState(stateObj, "seite 2", this.mainLayer.getCurrentContext().getUrl());
		}
		
//		public createContext(html: string, newGroup: boolean = false): Context {
////			if (newGroup) {
////				this.currentContentGroup = new ContentGroup();
////				this.additonalContentGroups.push(this.currentContentGroup);
////			}
//			
//			return this.currentLayer.createContext(html, bla);
//		}
		
		public getMainLayer(): Layer {
			return this.mainLayer;
		}
	}
	
	export class Layer {
		private jqContentGroup: JQuery;
		private contents: Array<Context>;
		private onNewContextCallbacks: Array<OnNewContextCallback>;
		
		constructor(jqContentGroup: JQuery) {
			this.contents = new Array<Context>();
			this.onNewContextCallbacks = new Array<OnNewContextCallback>();
			this.jqContentGroup = jqContentGroup;
		}
		
		public createContext(html: string, url: string): Context {
			var jqContent = $("<div/>", { "html": html });
			this.jqContentGroup.append(jqContent);
			var content = new Context(jqContent, url);
			
			this.contents.push(content);
			
			for (var i in this.onNewContextCallbacks) {
				this.onNewContextCallbacks[i](content);
			}
			
			return content;
		}
		
		public clear() {
			for (var i in this.contents) {
				this.contents[i].dispose();
			}
		}
		
		public dispose() {
			this.contents = new Array<Context>();
			this.jqContentGroup.remove();
		}
		
		public getCurrentContext(): Context {
			if (this.contents.length == 0) {
				throw new Error("no context available");
			}
			
			return this.contents[this.contents.length - 1];
		}
		
		public onNewContext(onNewContextCallback: OnNewContextCallback) {
			this.onNewContextCallbacks.push(onNewContextCallback);
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