namespace rocket.display {

    export class Initializer {
        
		public scan(jqContainer: JQuery) {
			var that = this;
			var curGroupContainer = null;
			
			jqContainer.find(".rocket-group-simple, .rocket-group-main, .rocket-group-autonomic").each(function () {
				var jqElem = $(this);
				var group = Group.from(jqElem);
				
				if (!jqElem.hasClass(".rocket-group-main")) {
					curGroupContainer = null;
					return;					
				}
				
				if (curGroupContainer === null) {
					var jqGroupNav = $("<ul />", {}).insertBefore(jqElem);
					curGroupContainer = new GroupContainer(jqGroupNav);
				}
				
				curGroupContainer.addGroup(group);
			});
		}
		
    }
    
    class GroupContainer {
    	private jqGroupNav: JQuery;
    
    	public constructor(jqGroupNav: JQuery) {
    		this.jqGroupNav = jqGroupNav;
    	}
    	
    	public 
    }
    
}