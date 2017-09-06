namespace rocket {
	var container: rocket.cmd.Container;
	var executor: rocket.cmd.Executor;
	var initializer: rocket.display.Initializer;
	
	jQuery(document).ready(function ($) {
		var jqContainer = $("#rocket-content-container");
		container = new rocket.cmd.Container(jqContainer);
		executor = new rocket.cmd.Executor(container);
		var monitor: rocket.cmd.Monitor = new rocket.cmd.Monitor(executor);

		monitor.scanMain($("#rocket-global-nav"), container.getMainLayer());
		monitor.scan(jqContainer);
		n2n.dispatch.registerCallback(function () {
			monitor.scan(jqContainer);
		});
		
		initializer = new rocket.display.Initializer(container, jqContainer.data("error-tab-title"), 
				jqContainer.data("display-error-label"));
		initializer.scan();
		
		n2n.dispatch.registerCallback(function () {
			initializer.scan();
		});
		
		(function () {
			$(".rocket-impl-overview").each(function () {
				rocket.impl.overview.OverviewContext.from($(this));
			});
			
			n2n.dispatch.registerCallback(function () {
				$(".rocket-impl-overview").each(function () {
					rocket.impl.overview.OverviewContext.from($(this));
				});
			});
		}) ();
		
		(function () {
			$("form.rocket-impl-form").each(function () {
				rocket.impl.Form.scan($(this));
			});
			
			n2n.dispatch.registerCallback(function () {
				$("form.rocket-impl-form").each(function () {
					rocket.impl.Form.scan($(this));
				});
			});
		}) ();
		
		(function () {
			$(".rocket-impl-to-many").each(function () {
				rocket.impl.ToMany.from($(this));
			});
			
			n2n.dispatch.registerCallback(function () {
				$(".rocket-impl-to-many").each(function () {
					rocket.impl.ToMany.from($(this));
				});
			});
		}) ();
	});
	
	export function scan(context: rocket.cmd.Context = null) {
		initializer.scan();
	}
	
	export function getContainer(): rocket.cmd.Container {
		return container;
	}
	
	export function layerOf(elem: HTMLElement): rocket.cmd.Layer {
		return rocket.cmd.Layer.findFrom($(elem));
	}
	
	export function contextOf(elem: HTMLElement): rocket.cmd.Context {
		return rocket.cmd.Context.findFrom($(elem));
	}
	
	export function handleErrorResponse(url: string, responseObject: any) {
		container.handleError(url, responseObject.responseText);
	}
	
	export function exec(url: string, config: rocket.cmd.ExecConfig = null) {
		executor.exec(url, config);
	}
	
	export function analyzeResponse(currentLayer: rocket.cmd.Layer, response: Object, targetUrl: string, 
			targetContext: rocket.cmd.Context = null): boolean {
		return executor.analyzeResponse(currentLayer, response, targetUrl, targetContext);
	}

}
