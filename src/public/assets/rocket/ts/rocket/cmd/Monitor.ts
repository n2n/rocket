namespace Rocket.Cmd {
	var $ = jQuery;
	
	export class Monitor {
		private executor: Executor;
		
		constructor(executor: Executor) {
			this.executor = executor;
		}
		
		public scanMain(jqContent: JQuery, layer: Layer) {
			var that = this;
            jqContent.find("a.rocket-ajah").each(function () {
                (new LinkAction(that.executor, jQuery(this), layer)).activate();
            });
		}
		
		public scan(jqContainer: JQuery) {
			var that = this;
			jqContainer.find("a.rocket-ajah").each(function () {
				CommandAction.from($(this), that.executor);
			});
		}
	}
	
    class LinkAction {
		private executor: Executor;
        private jqA: JQuery;
		private layer: Layer;
        
        constructor(executor: Executor, jqA: JQuery, layer: Layer) {
			this.executor = executor;
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
			this.executor.exec(url, { currentLayer: this.layer });
		}
    }
	
	class CommandAction {
		private executor: Executor;
		private jqElem: JQuery;
		
		public constructor(executor: Executor, jqElem: JQuery) {
			this.executor = executor;
			this.jqElem = jqElem;
			
			var that = this;
			jqElem.click(function (e) {
				that.handle();
				return false;
			});
		}
		
		private handle() {
			var url = this.jqElem.attr("href");
			var context = Context.findFrom(this.jqElem);
			if (context === null) {
				throw new Error("Command belongs to no Context.");
			}
			
			this.executor.exec(url, { currentContext: context });
		}
		
		public static from(jqElem: JQuery, executor: Executor): CommandAction {
			var commandAction = jqElem.data("rocketCommandAction");
			if (commandAction) return commandAction;
			
			commandAction = new CommandAction(executor, jqElem);
			jqElem.data("rocketCommandAction", commandAction);
			return commandAction;
		}
	}
}