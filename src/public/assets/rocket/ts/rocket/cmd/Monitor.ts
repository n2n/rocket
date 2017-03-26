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
		
		public scanContent(content: Content) {
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
				that.handle();
				e.stopImmediatePropagation();
                e.stopPropagation();
				return false;
            });
        }
		
		private handle() {
			var that = this;
			$.ajax({
				"url": this.jqA.attr("href"),
				"dataType": "json"
			}).fail(function (data) {
				alert(data);
			}).done(function (data) {
				that.layer.clear();
				that.layer.createContent(n2n.ajah.analyze(data));
				n2n.ajah.update();
			});
		}
    }
}