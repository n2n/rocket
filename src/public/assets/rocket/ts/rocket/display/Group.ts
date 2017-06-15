namespace rocket.display {

    export class Group {
        private jqGroup: JQuery;
		private onShowCallbacks: Array<(Group) => any> = new Array<(Group) => any>();
		private onHideCallbacks: Array<(Group) => any> = new Array<(Group) => any>();
		
        constructor(jqGroup: JQuery) {
			this.jqGroup = jqGroup;
			
			jqGroup.addClass("rocket-group");
			jqGroup.data("rocketGroup", this);
        }
		
		public getTitle() {
			return this.jqGroup.find("label:first").text();
		}
		
		public show() {
			this.jqGroup.show();
			
			for (var i in this.onShowCallbacks) {
				this.onShowCallbacks[i](this);
			}
		}
		
		public hide() {
			this.jqGroup.hide();
			
			for (var i in this.onHideCallbacks) {
				this.onHideCallbacks[i](this);
			}
		}
		
		public addChildGroup(group: Group) {
			var that = this;
			group.onShow(function () {
				that.show();
			});
		}
		
		public onShow(callback: (Group) => any) {
			this.onShowCallbacks.push(callback);
		}
		
		public onHide(callback: (Group) => any) {
			this.onHideCallbacks.push(callback);
		}
		
		public static from(jqElem: JQuery, create: boolean = true): Group {
			var rocketGroup = jqElem.data("rocketGroup");
			if (rocketGroup) return rocketGroup;
		
			if (!create) return null;
			
			rocketGroup = new Group(jqElem);
			jqElem.data("rocketCommandAction", rocketGroup);
			return rocketGroup;
		}
		
		public static findFrom(jqElem: JQuery): Group {
			jqElem = jqElem.parents(".rocket-group");
			
			var group = jqElem.data("rocketGroup");
			if (group instanceof Group) {
				return group;
			}
			
			return null;
		}
    }
	
	export class Field {
		private jqField: JQuery;
		private group: Group;
		
		constructor(jqField: JQuery, group: Group = null) {
			this.jqField = jqField;
			this.group = group;
			
			jqField.addClass("rocket-field");
			jqField.data("rocketField", this);
        }
		
		public setGroup(group: Group) {
			this.group = group;
		}
		
		public getGroup(): Group {
			return this.group;
		}
		
		public getLabel(): string {
			return this.jqField.find("label:first").text();	
		}
		
		public scrollTo() {
			var top = this.jqField.offset().top;
			var maxOffset = top - 50;
			
			var height = this.jqField.outerHeight();
			var margin = $(window).height() - height;
			
			var offset = top - (margin / 2);
			
			if (maxOffset < offset) {
				offset = maxOffset;
			}
			
			$("html, body").animate({
		    	"scrollTop": offset
		    }, 250);
		}
		
		public highlight() {
			this.jqField.addClass("rocket-highlighted");
		}
		
		public unhighlight(slow: boolean = false) {
			this.jqField.removeClass("rocket-highlighted");
			
			if (slow) {
				this.jqField.addClass("rocket-highlight-remember");	
			} else {
				this.jqField.removeClass("rocket-highlight-remember");
			}
		}
		
		public static from(jqElem: JQuery, create: boolean = true): Field {
			var rocketField = jqElem.data("rocketField");
			if (rocketField instanceof Field) return rocketField;
		
			if (!create) return null;
			
			return new Field(jqElem, Group.findFrom(jqElem));
		}
		
		public static findFrom(jqElem: JQuery): Field {
			jqElem = jqElem.parents(".rocket-field");
			 
			var field: Field = jqElem.data("rocketField");
			if (field instanceof Field) {
				return field;
			}
			
			return null;
		}
    }
	
}