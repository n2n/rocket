namespace rocket.cmd {
	var $ = jQuery;
	
	export class Monitor {
		private content: Content;
		
		constructor(content: Content) {
			this.content = content;
		}
		
		public scan(jqContent: JQuery) {
            jqContent.find("a.rocket-action").each(function () {
                (new LinkAction(jQuery(this))).activate();
            });
		}
	}
	
    class LinkAction {
        private jqA: JQuery;
        
        constructor(jqA: JQuery) {
            this.jqA = jqA;
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
			$.ajax({
				"url": this.jqA.attr("href"),
				"dataType": "json"
			}).fail(function (data) {
				alert(data);
			}).done(function (data) {
				alert(n2n.ajah.analyze(data));
				n2n.ajah.update();
			});
		}
    }
}