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
		
		public exec(url: string|Url, config: ExecConfig = null) {
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
				"url": url.toString(),
				"dataType": "json"
			}).fail(function (jqXHR, textStatus, data) {
				if (jqXHR.status != 200) {
                    config.currentLayer.getContainer().handleError(url.toString(), jqXHR.responseText);
					return;
				}
				
				alert("Not yet implemented press F5 after ok.");
			}).done(function (data, textStatus, jqXHR) {
				that.analyzeResponse(config.currentLayer, data, url.toString(), targetContext);
				
				if (config.done) {
					config.done(new ExecResult(null, targetContext));
				}
			});
		}
		
		public analyzeResponse(currentLayer: Layer, response: Object, targetUrl: string, targetContext: Context = null): boolean {
			if (typeof response["additional"] === "object") {
				if (this.execDirectives(currentLayer, response["additional"])) {
					if (targetContext !== null) targetContext.close();
					return true;
				}
			}
			
			if (targetContext === null) {
				targetContext = currentLayer.getContextByUrl(targetUrl);
				if (targetContext !== null) {
					currentLayer.pushHistoryEntry(targetUrl);
				}
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
	
	export interface ExecConfig {
		forceReload?: boolean; 
		showLoadingContext?: boolean; 
		createNewLayer?: boolean;
		currentLayer?: Layer;
		currentContext?: Context;
		done?: (ExecResult) => any;
	}
	
	export class ExecResult {
		constructor(order, private _context: Context) {
		}
		
		get context(): Context {
			return this._context;
		}
	}
}