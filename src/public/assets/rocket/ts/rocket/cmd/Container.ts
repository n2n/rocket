namespace rocket.cmd {
	
	export class Container {
		private jqContainer: JQuery;
		private mainLayer: Layer;
		private additonalLayers: Array<Layer>
		private currentLayer: Layer;
		
		constructor(jqContainer: JQuery) {
			this.jqContainer = jqContainer;
			
			this.mainLayer = new Layer(this.jqContainer.find(".rocket-main-layer"));
		}
		
		public createContent(html: string, newGroup: boolean = false): Content {
//			if (newGroup) {
//				this.currentContentGroup = new ContentGroup();
//				this.additonalContentGroups.push(this.currentContentGroup);
//			}
			
			return this.currentLayer.createContent(html);
		}
		
		public getMainLayer(): Layer {
			return this.mainLayer;
		}
	}
	
	export class Layer {
		private jqContentGroup: JQuery;
		private contents: Array<Content>
		
		constructor(jqContentGroup: JQuery) {
			this.contents = new Array<Content>();
			this.jqContentGroup = jqContentGroup;
		}
		
		public createContent(html: string): Content {
			var jqContent = $("<div/>", {
				"class": "rocket-context",
				"html": html
			});
			this.jqContentGroup.append(jqContent);
			var content = new Content(jqContent);
			
			this.contents.push(content);
			return content;
		}
		
		public clear() {
			for (var i in this.contents) {
				this.contents[i].dispose();
			}
		}
		
		public dispose() {
			this.contents = new Array<Content>();
			this.jqContentGroup.remove();
		}
	}
	
	export class Content {
		private jqContent: JQuery;
		
		constructor(jqContent: JQuery) {
			this.jqContent = jqContent;
		}
		
		public hide() {
			this.jqContent.hide();	
		}
		
		public dispose() {
			this.jqContent.remove();
		}
	}
	
}