namespace rocket.cmd {

	export class Monitor {
		private jqContainer: JQuery;
		
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
			this.jqA.attr("href");
		}
    }
}