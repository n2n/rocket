namespace rocket.cmd {
	var $ = jQuery;
	
	export class Monitor {
		private container: Container;
		
		constructor(container: Container) {
			this.container = container;
		}
		
		public scanMain(jqContent: JQuery, layer: Layer) {
			var that = this;
            jqContent.find("a.rocket-action").each(function () {
                (new LinkAction(jQuery(this), layer)).activate();
            });
		}
		
	}
	
    class LinkAction {
        private jqA: JQuery;
		private layer: Layer;
        
        constructor(jqA: JQuery, layer: Layer) {
            this.jqA = jqA;
			this.layer = layer;
        }
        
        public activate() {
			var that = this;
            this.jqA.click(function (e: Event) {
				e.stopImmediatePropagation();
                e.stopPropagation();
				that.handle();
				return false;
            });
        }
		
		private handle() {
			var url = this.jqA.attr("href");
			this.layer.exec(url);
		}
    }
}