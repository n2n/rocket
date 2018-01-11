namespace Rocket.Impl.Translation {
	
	export class Translator {
		
		constructor(private container: Rocket.Cmd.Container) {
		}
		
		scan() {
			for (let context of this.container.getAllZones()) {
				let elems: Array<HTMLElement> = context.jQuery.find(".rocket-impl-translation-manager").toArray();
				let elem;
				while (elem = elems.pop()) {
					this.initTm($(elem), context);
				}
				
				let jqViewControl = context.menu.toolbar.getJqControls().find(".rocket-impl-translation-view-control");
				
				let jqTranslatables = context.jQuery.find(".rocket-impl-translatable");
				if (jqTranslatables.length == 0) {
					jqViewControl.hide();
					continue;
				}

				jqViewControl.show();
				
				if (jqViewControl.length == 0) {
					jqViewControl = $("<div />", { "class": "rocket-impl-translation-view-control" });
					context.menu.toolbar.getJqControls().show().append(jqViewControl);
				}
				
				let viewMenu = ViewMenu.from(jqViewControl);
				jqTranslatables.each((i, elem) => {
					viewMenu.registerTranslatable(Translatable.from($(elem)));
				});
			}
		}
		
		private initTm(jqElem: JQuery, context: Rocket.Cmd.Zone) {
			let tm = TranslationManager.from(jqElem);
			
			let se = Rocket.Display.StructureElement.of(jqElem);
			
			let jqBase = null;
			if (!se) {
				jqBase = context.jQuery;
			} else {
				jqBase = jqElem;
			}
			
			jqBase.find(".rocket-impl-translatable").each((i, elem) => {
				tm.registerTranslatable(Translatable.from($(elem)));
			});
		}
	}
	
	export class Translatable {
		private copyUrls: { [localeId: string]: UrlDef } = {};
		private _contents: { [localeId: string]: TranslatedContent } = {}
		
		constructor(private jqElem: JQuery) {
			let copyUrlDefs = jqElem.data("rocket-impl-copy-urls");
			for (let localeId in copyUrlDefs) {
				this.copyUrls[localeId] = {
					label: copyUrlDefs[localeId].label,
					copyUrl: Jhtml.Url.create(copyUrlDefs[localeId].copyUrl)
				};
			}
		}
		
		get jQuery(): JQuery {
			return this.jqElem;
		}
		
		get localeIds(): Array<string> {
			return Object.keys(this._contents);
		}
		
		get contents(): Array<TranslatedContent> {
			let O: any = Object;
			return O.values(this._contents);
		}
		
		set visibleLocaleIds(localeIds: Array<string>) {
			for (let content of this.contents) {
				content.visible = -1 < localeIds.indexOf(content.localeId);
			}
		}
		
		get visibleLocaleIds() {
			let localeIds = new Array<string>();
			
			for (let content of this.contents) {
				if (!content.visible) continue;
				
				localeIds.push(content.localeId);
			}
			
			return localeIds;
		}
		
		set activeLocaleIds(localeIds: Array<string>) {
			for (let content of this.contents) {
				content.active = -1 < localeIds.indexOf(content.localeId);
			}
		}
		
		get activeLocaleIds() {
			let localeIds = new Array<string>();
			
			for (let content of this.contents) {
				if (!content.active) continue;
				
				localeIds.push(content.localeId);
			}
			
			return localeIds;
		}
		
		public scan() {
			this.jqElem.children().each((i, elem) => {
				let jqElem: JQuery = $(elem);
				let localeId = jqElem.data("rocket-impl-locale-id");
				if (!localeId || this._contents[localeId]) return;
				
				let tc = this._contents[localeId] = new TranslatedContent(localeId, jqElem);
				tc.drawCopyControl(this.copyUrls);
			});
		}
		
		static from(jqElem: JQuery): Translatable {
			let translatable = jqElem.data("rocketImplTranslatable");
			if (translatable instanceof Translatable) {
				return translatable;
			}
			
			translatable = new Translatable(jqElem);
			jqElem.data("rocketImplTranslatable", translatable);
			translatable.scan();
			return translatable;
		}
	}
	
	interface UrlDef { 
		label: string, 
		copyUrl: Jhtml.Url 
	}
	
	class TranslatedContent {
//		private jqTranslation: JQuery;
		private _propertyPath: string;
		private _fieldJq: JQuery;
		private jqEnabler: JQuery = null;
		private copyControlJq: JQuery = null;
		private changedCallbacks: Array<() => any> = [];
		private _visible: boolean = true;
		
		constructor(private _localeId: string, private elemJq: JQuery) {
			Display.StructureElement.from(elemJq, true);
//			this.jqTranslation = jqElem.children(".rocket-impl-translation");
			this._propertyPath = elemJq.data("rocket-impl-property-path");
			this._fieldJq = elemJq.children("div");
		}
		
		get jQuery(): JQuery {
			return this.elemJq;
		}
		
		get fieldJq(): JQuery {
			return this._fieldJq;
		}
		
		replaceField(newFieldJq: JQuery) {
			this._fieldJq.replaceWith(newFieldJq);
			this._fieldJq = newFieldJq;
		}
		
		get localeId(): string {
			return this._localeId;
		}
		
		get propertyPath(): string {
			return this._propertyPath;
		}
		
		get prettyLocaleId(): string {
			return this.elemJq.find("label:first").text();
		}
		
		get localeName(): string {
			return this.elemJq.find("label:first").attr("title");
		}
		
		get visible(): boolean {
			return this._visible;
		}
		
		set visible(visible: boolean) {
			if (visible) {
				if (this._visible) return;
				this._visible = true;
				
				this.elemJq.show();
				this.triggerChanged();
				return;
			}
			
			if (!this._visible) return;
			
			this._visible = false;
			this.elemJq.hide();
			this.triggerChanged();
		}
		
		get active(): boolean {
			return this.jqEnabler ? false : true;
		}
		
		set active(active: boolean) {
			if (active) {
				if (this.jqEnabler) {
					this.jqEnabler.remove();
					this.jqEnabler = null;
					this.triggerChanged();
				}
				
				if (this.copyControlJq) {
					this.copyControlJq.show();
				}
				
				return;
			}
			
			if (!this.jqEnabler) {
				this.jqEnabler = $("<button />", {
					"class": "rocket-impl-enabler",
					"type": "button",
					"text": " " + this.elemJq.data("rocket-impl-activate-label"),
					"click": () => { this.active = true} 
				}).prepend($("<i />", { "class": "fa fa-language", "text": "" })).appendTo(this.elemJq);
				
				this.triggerChanged();
			}
			
			if (this.copyControlJq) {
				this.copyControlJq.show();
			}
		}
		
		private copyControl: CopyControl;
		
		drawCopyControl(urlDefs: { [localeId: string]: UrlDef }) {
			for (let localeId in urlDefs) {
				if (localeId == this.localeId) continue;
				
				if (!this.copyControl) {
					this.copyControl = new CopyControl(this);
					this.copyControl.draw(this.elemJq.data("rocket-impl-copy-tooltip"));
				}
				
				this.copyControl.addUrlDef(urlDefs[localeId]);
			}
		}
		
		private triggerChanged() {
			for (let callback of this.changedCallbacks) {
				callback();
			}
		}
		
		public whenChanged(callback: () => any) {
			this.changedCallbacks.push(callback);
		}
	}
	
	class CopyControl {
		
		private elemJq: JQuery;
		private menuUlJq: JQuery;
		private toggler: Toggler;
	
		constructor(private translatedContent: TranslatedContent) {
			
		}
		
		draw(tooltip: string) {
			this.elemJq = $("<div></div>", { class: "rocket-impl-translation-copy-control" });
			this.translatedContent.jQuery.prepend(this.elemJq);
			
			let buttonJq = $("<button />", { "type": "button", "class": "btn btn-secondary" })
					.append($("<i></i>", { class: "fa fa-copy", title: tooltip }));
			let menuJq = $("<div />", { class: "rocket-impl-translation-copy-control" })
					.append(this.menuUlJq = $("<ul></ul>"))
					.append($("<div />", { class: "rocket-impl-tooltip", text: tooltip }));
			
			this.toggler = Toggler.simple(buttonJq, menuJq);
			
			this.elemJq.append(buttonJq);
			this.elemJq.append(menuJq);
			
//			if (!this.translatedContent.active) {
//				this.hide();
//			}
		}
		
		addUrlDef(urlDef: UrlDef) {
			let url = this.completeCopyUrl(urlDef.copyUrl);
			this.menuUlJq.append($("<li/>").append($("<a />", {
				"text": urlDef.label
			}).append($("<i></i>", { class: "fa fa-mail-forward"})).click((e) => {
				e.stopPropagation();
				this.copy(url);
				this.toggler.close();
			})));
		}
		
		
		private completeCopyUrl(url: Jhtml.Url) {
			return url.extR(null, {
				propertyPath: this.translatedContent.propertyPath,
				toN2nLocale: this.translatedContent.localeId
			});
		}
		
		private loaderJq: JQuery;
		
		private copy(url: Jhtml.Url) {
			if (this.loaderJq) return;
			
			this.loaderJq = $("<div />", {
				class: "rocket-load-blocker"
			}).append($("<div></div>", { class: "rocket-loading" })).appendTo(this.translatedContent.jQuery);
			
			Jhtml.lookupModel(url).then((model: Jhtml.Model) => {
				this.replace(model.snippet);
			});
		}
		
		private replace(snippet: Jhtml.Snippet) {
			let newFieldJq = $(snippet.elements).children();
			this.translatedContent.replaceField(newFieldJq);
			snippet.elements = newFieldJq.toArray();
			snippet.markAttached();
			
			this.loaderJq.remove();
			this.loaderJq = null;
		}
	}
}