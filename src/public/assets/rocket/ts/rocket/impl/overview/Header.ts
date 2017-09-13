namespace rocket.impl.overview {
	import cmd = rocket.cmd;
	import impl = rocket.impl;
	
	var $ = jQuery;

	export class Header {
		private jqElem
		
		constructor(private overviewContent: OverviewContent) {
			
		}
		
		draw(jqElem: JQuery) {
			jqElem.find("rocket-impl-quicksearch");
		}
	}
	
	class QuickSearch {
		constructor(private overviewContent: OverviewContent, jqForm) {
		}	
	}
}