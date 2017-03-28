namespace rocket {
	jQuery(document).ready(function ($) {
		var container = new rocket.cmd.Container($("#rocket-content-container"));
		var monitor: rocket.cmd.Monitor = new rocket.cmd.Monitor(container);

		monitor.scanMain($("#rocket-global-nav"), container.getMainLayer());
		
		
		(function () {
			var overviewToolElems = new Array<Element>();
			
			$(".rocket-overview-tools").each(function () {
				overviewToolElems.push(this);
				
				new rocket.impl.OverviewContext($(this));
			});
			
			n2n.dispatch.registerCallback(function () {
				$(".rocket-overview-tools").each(function () {
					if (-1 < overviewToolElems.indexOf(this)) {
						return;
					}
					
					new rocket.impl.OverviewContext($(this));
				});
			});
		}) ();
	});
}