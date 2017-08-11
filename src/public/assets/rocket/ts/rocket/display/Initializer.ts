namespace rocket.display {
	import Container = rocket.cmd.Container;
	import Context = rocket.cmd.Context;
	import AdditionalTab = rocket.cmd.AdditionalTab;
	
    export class Initializer {
        private container: Container;
		private errorTabTitle: string;
		private displayErrorLabel: string;
		private errorIndexes: Array<ErrorIndex>;
		
		constructor(container: Container, errorTabTitle: string, displayErrorLabel: string) {
			this.container = container;
			this.errorTabTitle = errorTabTitle;
			this.displayErrorLabel = displayErrorLabel;
			this.errorIndexes = new Array<ErrorIndex>();
		}
		
		public scan() {
			var errorIndex = null;
			while (undefined !== (errorIndex = this.errorIndexes.pop())) {
				errorIndex.getTab().dispose();
			}  
			
			var contexts = this.container.getAllContexts();
			for (var i in contexts) {
				this.scanContext(contexts[i]);
			}
		}
		
		private scanContext(context: Context) {
			var that = this;
			
			var i = 0;
			
			var jqContext = context.getJQuery();
			
			jqContext.find(".rocket-group-simple, .rocket-group-main, .rocket-group-autonomic, .rocket-field").each(function () {
				var jqElem = $(this);
				var structureElement = StructureElement.from(jqElem);
				
				if (structureElement !== null) return;
				
				if (!jqElem.hasClass("rocket-group-main")) {
					Initializer.createStructureElement(jqElem);
					return;
				}
				
				Initializer.scanGroupNav(jqElem.parent());
			});
			
			
			var errorIndex: ErrorIndex = null;
			
			jqContext.find(".rocket-message-error").each(function () {
				var structureElement = StructureElement.findFrom($(this));
				
				if (errorIndex === null) {
					errorIndex = new ErrorIndex(context.createAdditionalTab(that.errorTabTitle), that.displayErrorLabel);
					that.errorIndexes.push(errorIndex);
				}
				
				errorIndex.addError(structureElement, $(this).text());
			});
		}
		
		private static createStructureElement(jqElem: JQuery): StructureElement {
			var structureElement = StructureElement.from(jqElem, 
					jqElem.hasClass("rocket-group-simple") || jqElem.hasClass("rocket-group-main") 
							|| jqElem.hasClass("rocket-group-autonomic"), 
					jqElem.hasClass("rocket-field"));
			
			return structureElement;
		}
		
		private static scanGroupNav(jqContainer: JQuery) {
			var curGroupNav = null;
			
			jqContainer.children(".rocket-group-simple, .rocket-group-main, .rocket-group-autonomic").each(function () {
				var jqElem = $(this);
				if (!jqElem.hasClass("rocket-group-main")) {
					curGroupNav = null;
					return;
				}
				
				if (curGroupNav === null) {
					curGroupNav = GroupNav.fromMain(jqElem);
				}
				
				var group = StructureElement.from(jqElem);
				if (group === null) {	
					curGroupNav.registerGroup(Initializer.createStructureElement(jqElem));
				}
			});
			
			return curGroupNav;
		}
    }
    
    class GroupNav {
    	private jqGroupNav: JQuery;
		private groups: Array<StructureElement>;
    
    	public constructor(jqGroupNav: JQuery) {
    		this.jqGroupNav = jqGroupNav;
			this.groups = new Array<StructureElement>();
			
			jqGroupNav.addClass("rocket-main-group-nav");
			jqGroupNav.hide();
    	}
    	
    	public registerGroup(group: StructureElement) {
			this.groups.push(group);
			if (this.groups.length == 2) {
				this.jqGroupNav.show();
			}
			
			var jqLi = $("<li />", {
				"text": group.getTitle(),
				"clss": { "cursor": "pointer" }
			});
			
			this.jqGroupNav.append(jqLi);
			
			var that = this;
			
			jqLi.click(function () {
				group.show();
			});
			
			group.onShow(function () {
				jqLi.addClass("rocket-active");
				
				for (var i in that.groups) {
					if (that.groups[i] !== group) {
						that.groups[i].hide();
					}
				}
			});
			
			group.onHide(function () {
				jqLi.removeClass("rocket-active");
			});
			
			if (this.groups.length == 1) {
				group.show();
			}
		}
		
		public static fromMain(jqElem: JQuery, create: boolean = true) {
			var groupNav = null;

			var jqPrev = jqElem.prev(".rocket-main-group-nav");
			if (jqPrev.length > 0) {
				groupNav = jqPrev.data("rocketGroupNav");
			}
				
			if (groupNav) return groupNav;
			
			if (!create) return null;
			
			var jqUl = $("<ul />").insertBefore(jqElem);
			
			return new GroupNav(jqUl);
		}
    }
    
	
	class ErrorIndex {
		private jqIndex: JQuery;
		private tab: AdditionalTab;
		private displayErrorLabel: string;
		
		constructor(tab: AdditionalTab, displayErrorLabel: string) {
			this.tab = tab;
			this.displayErrorLabel = displayErrorLabel;
		}
		
		public getTab(): AdditionalTab {
			return this.tab;
		}
		
		public addError(field: StructureElement, errorMessage: string) {
			var jqElem = $("<div />", {
				"class": "rocket-error-index-entry",
				"css": { "cursor": "pointer" }
			}).append($("<div />", { 
				"text": errorMessage 
			})).append($("<div />", {
				"text": this.displayErrorLabel
			}));
			
			this.tab.getJqContent().append(jqElem);
		
			var clicked = false;
			
			jqElem.mouseenter(function () {
				field.highlight();
			});
			
			jqElem.mouseleave(function () {
				field.unhighlight(clicked);
				clicked = false;
			});
			
			jqElem.click(function () {
				clicked = true;
				field.show(true);
				field.scrollTo();
			});
		}
	}
}