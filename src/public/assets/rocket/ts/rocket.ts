namespace Rocket {
	var container: Rocket.Cmd.Container;
	var blocker: Rocket.Cmd.Blocker;
	var executor: Rocket.Cmd.Executor;
	var initializer: Rocket.Display.Initializer;
	
	jQuery(document).ready(function ($) {
		var jqContainer = $("#rocket-content-container");
		container = new Rocket.Cmd.Container(jqContainer);
		
		blocker = new Rocket.Cmd.Blocker(container);
		blocker.init($("body"));
		
		executor = new Rocket.Cmd.Executor(container);
		var monitor: Rocket.Cmd.Monitor = new Rocket.Cmd.Monitor(executor);

		monitor.scanMain($("#rocket-global-nav"), container.mainLayer);
		monitor.scan(jqContainer);
		n2n.dispatch.registerCallback(function () {
			monitor.scan(jqContainer);
		});
		
		initializer = new Rocket.Display.Initializer(container, jqContainer.data("error-tab-title"), 
				jqContainer.data("display-error-label"));
		initializer.scan();
		
		n2n.dispatch.registerCallback(function () {
			initializer.scan();
		});
		
		(function () {
			$(".rocket-impl-overview").each(function () {
				Rocket.Impl.Overview.OverviewContext.from($(this));
			});
			
			n2n.dispatch.registerCallback(function () {
				$(".rocket-impl-overview").each(function () {
					Rocket.Impl.Overview.OverviewContext.from($(this));
				});
			});
		}) ();
		
		(function () {
			$("form.rocket-impl-form").each(function () {
				Rocket.Impl.Form.from($(this));
			});
			
			n2n.dispatch.registerCallback(function () {
				$("form.rocket-impl-form").each(function () {
					Rocket.Impl.Form.from($(this));
				});
			});
		}) ();
		
		(function () {
			$(".rocket-impl-to-many").each(function () {
				Rocket.Impl.Relation.ToMany.from($(this));
			});
			
			n2n.dispatch.registerCallback(function () {
				$(".rocket-impl-to-many").each(function () {
					Rocket.Impl.Relation.ToMany.from($(this));
				});
			});
		}) ();
		
		(function () {
			let t = new Rocket.Impl.Translator(container);
			t.scan();
			
			n2n.dispatch.registerCallback(function () {
				t.scan();	
			});
		}) ();
	});
	
	export function scan(context: Rocket.Cmd.Context = null) {
		initializer.scan();
	}
	
	export function getContainer(): Rocket.Cmd.Container {
		return container;
	}
	
	export function layerOf(elem: HTMLElement): Rocket.Cmd.Layer {
		return Rocket.Cmd.Layer.findFrom($(elem));
	}
	
	export function contextOf(elem: HTMLElement): Rocket.Cmd.Context {
		return Rocket.Cmd.Context.findFrom($(elem));
	}
	
	export function handleErrorResponse(url: string, responseObject: any) {
		container.handleError(url, responseObject.responseText);
	}
	
	export function exec(url: string, config: Rocket.Cmd.ExecConfig = null) {
		executor.exec(url, config);
	}
	
	export function analyzeResponse(currentLayer: Rocket.Cmd.Layer, response: Object, targetUrl: string, 
			targetContext: Rocket.Cmd.Context = null): boolean {
		return executor.analyzeResponse(currentLayer, response, targetUrl, targetContext);
	}

}
