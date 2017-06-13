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
		
		public getGroup(): Group {
			return this.group;
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
		
		public static from(jqElem: JQuery, create: boolean = true): Group {
			var rocketGroup = jqElem.data("rocketGroup");
			if (rocketGroup) return rocketGroup;
		
			if (!create) return null;
			
			rocketGroup = new Group(jqElem);
			jqElem.data("rocketCommandAction", rocketGroup);
			return rocketGroup;
		}
		
		public static findFrom(jqElem: JQuery): Group {
			jqElem = jqElem.parents(".rocket-field");
			
			 
			var field = jqElem.data("rocketField");
			if (field instanceof Field) {
				return field;	
			}
			
			return null;
		}
    }
}