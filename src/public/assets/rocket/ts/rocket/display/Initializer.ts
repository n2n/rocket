namespace rocket.display {

    export class Initializer {
        
		public static scan(jqContainer: JQuery) {
			var that = this;
			
			var i = 0;
			
			jqContainer.find(".rocket-group-simple, .rocket-group-main, .rocket-group-autonomic").each(function () {
				var jqElem = $(this);
				var group = Group.from(jqElem, false);
				
				if (group !== null) return;
				
				
				if (!jqElem.hasClass("rocket-group-main")) {
					Initializer.createGroup(jqElem);
					return;					
				}
				
				Initializer.scanGroupNav(jqElem.parent());
			});
			
			
			
			jqContainer.find(".rocket-message-error").each(function () {
				
			});
		}
		
		private static createGroup(jqElem: JQuery) {
			var group = Group.from(jqElem, true);
			
			var parentGroup = Group.findFrom(jqElem);
			
			if (parentGroup !== null) {
				parentGroup.addChildGroup(group);
			}
			
			return group;
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
				
				var group = Group.from(jqElem, false);
				if (group === null) {	
					curGroupNav.registerGroup(Initializer.createGroup(jqElem));
				}
			});
			
			return curGroupNav;
		}
    }
    
    class GroupNav {
    	private jqGroupNav: JQuery;
		private groups: Array<Group>;
    
    	public constructor(jqGroupNav: JQuery) {
    		this.jqGroupNav = jqGroupNav;
			this.groups = new Array<Group>();
			
			jqGroupNav.addClass("rocket-main-group-nav");
			jqGroupNav.hide();
    	}
    	
    	public registerGroup(group: Group) {
			this.groups.push(group);
			if (this.groups.length == 2) {
				this.jqGroupNav.show();
			}
			
			var jqLi = $("<li />", {
				"text": group.getTitle()
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
    
}