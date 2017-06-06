namespace rocket.display {

    export class Group {
        private jqGroup: JQuery;
		
        constructor(jqGroup: JQuery) {
			this.jqGroup = jqGroup;
			
			jqGroup.addClass("rocket-group");
			jqGroup.data("rocketGroup", this);
        }
		
		public static from(jqElem: JQuery): Group {
			var rocketGroup = jqElem.data("rocketGroup");
			if (rocketGroup) return rocketGroup;
			
			rocketGroup = new Group(jqElem);
			jqElem.data("rocketCommandAction", rocketGroup);
			return rocketGroup;
		}
		
		public static findFrom(jqElem: JQuery): Group {
			if (!jqElem.hasClass(".rocket-group")) {
				jqElem = jqElem.parents(".rocket-group");
			}
			
			var group = jqElem.data("rocketGroup");
			if (group === undefined) {
				return null;
			}
			
			return group;
		}
    }
    
}