namespace rocket.cmd {
	
	export class Executor {
		private container: Container;
		
		constructor(container: Container) {
			this.container = container;
		}
		
		private purifyExecConfig(config: ExecConfig): ExecConfig {
			config.forceReload = config.forceReload === true;
			config.showLoadingContext = config.showLoadingContext !== false
			config.createNewLayer = config.createNewLayer === true
			
			if (!config.currentLayer) {
				if (config.currentContext) {
					config.currentLayer = config.currentContext.getLayer();
				} else {
					config.currentLayer = this.container.getCurrentLayer();
				}
			}
			
			if (!config.currentContext) {
				config.currentContext = null;
			}
			
			return config;
		}
		
		public exec(url: string, config: ExecConfig = null) {
			config = this.purifyExecConfig(config);
			
			var targetContext = null;
			
			if (!config.createNewLayer) {
				targetContext = config.currentLayer.getContextByUrl(url);
			}
			
			if (targetContext !== null) {
				if (config.currentLayer.getCurrentContext() !== targetContext) {
					config.currentLayer.pushHistoryEntry(targetContext.getUrl());
				}
				
				if (!config.forceReload) {
					if (config.done) {
						setTimeout(function () { config.done(new ExecResult(null, targetContext)); }, 0);
					}
					
					return;
				}
			}
			
			if (targetContext === null && config.showLoadingContext) {
				targetContext = config.currentLayer.createContext(url);
				config.currentLayer.pushHistoryEntry(url);
			}
			
			if (targetContext !== null) {
				targetContext.clear(true);
			}
		
			var that = this;
			$.ajax({
				"url": url,
				"dataType": "json"
			}).fail(function (data) {
				targetContext.applyErrorHtml(data.responseText);
			}).done(function (data) {
				that.analyzeResponse(config.currentLayer, data, url, targetContext);
				
				if (config.done) {
					config.done(new ExecResult(null, targetContext));
				}
			});
		}
		
		private analyzeResponse(currentLayer: Layer, response: Object, targetUrl: string, targetContext: Context = null): boolean {
			if (typeof response["additional"] === "object") {
				if (this.execDirectives(currentLayer, response["additional"])) {
					if (targetContext !== null) targetContext.close();
					return true;
				} 
			}
			
			if (targetContext === null) {
				targetContext = currentLayer.getContextByUrl(targetUrl);
				currentLayer.pushHistoryEntry(targetUrl);
			}
			
			if (targetContext === null) {
				targetContext = currentLayer.createContext(targetUrl);
				currentLayer.pushHistoryEntry(targetUrl);
			}
			
			targetContext.applyHtml(n2n.ajah.analyze(response));
			n2n.ajah.update();
		}
		
		private execDirectives(currentLayer: Layer, info: any) {
			if (info.directive == "redirectBack") {
				var index = currentLayer.getCurrentHistoryIndex();
				
				if (index > 0) {
					this.exec(currentLayer.getHistoryUrlByIndex(index - 1), { "currentLayer": currentLayer });
					return true;
				}
				
				if (info.fallbackUrl) {
					this.exec(info.fallbackUrl, { "currentLayer": currentLayer });
					return true;
				}
				
				currentLayer.close();
			}
			
			return false;
		}
	}
	
	interface ExecConfig {
		forceReload?: boolean; 
		showLoadingContext?: boolean; 
		createNewLayer?: boolean;
		currentLayer?: Layer;
		currentContext?: Context;
		done?: (ExecResult) => any;
	}
	
	class ExecResult {
		constructor(order, context: Context) {
		}
	}
}