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
		private propertyPath: string;
		private fieldJq: JQuery;
		private jqEnabler: JQuery = null;
		private copyControlJq: JQuery = null;
		private changedCallbacks: Array<() => any> = [];
		private _visible: boolean = true;
		
		constructor(private _localeId: string, private jqElem: JQuery) {
			Display.StructureElement.from(jqElem, true);
//			this.jqTranslation = jqElem.children(".rocket-impl-translation");
			this.propertyPath = jqElem.data("rocket-impl-property-path");
			this.fieldJq = jqElem.children("div");
		}
		
		get localeId(): string {
			return this._localeId;
		}
		
		get prettyLocaleId(): string {
			return this.jqElem.find("label:first").text();
		}
		
		get localeName(): string {
			return this.jqElem.find("label:first").attr("title");
		}
		
		get visible(): boolean {
			return this._visible;
		}
		
		set visible(visible: boolean) {
			if (visible) {
				if (this._visible) return;
				this._visible = true;
				
				this.jqElem.show();
				this.triggerChanged();
				return;
			}
			
			if (!this._visible) return;
			
			this._visible = false;
			this.jqElem.hide();
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
					"text": " " + this.jqElem.data("rocket-impl-activate-label"),
					"click": () => { this.active = true} 
				}).prepend($("<i />", { "class": "fa fa-language", "text": "" })).appendTo(this.jqElem);
				
				this.triggerChanged();
			}
			
			if (this.copyControlJq) {
				this.copyControlJq.show();
			}
		}
		
		drawCopyControl(urlDefs: { [localeId: string]: UrlDef }) {
			let copyUlJq: JQuery;
			for (let localeId in urlDefs) {
				if (localeId == this.localeId) continue;
				
				if (!copyUlJq) {
					copyUlJq = this.drawCopyUlJq();
				}
				
				let urlDef = urlDefs[localeId];
				
				let url = this.completeCopyUrl(urlDef.copyUrl);
				copyUlJq.append($("<li/>").append($("<a />", {
					"text": urlDef.label
				}).click((e) => {
					e.stopPropagation();
					this.copy(url);
					copyUlJq.hide();
				})));
			}
		}
		
		private completeCopyUrl(url: Jhtml.Url) {
			return url.extR(null, {
				propertyPath: this.propertyPath,
				toN2nLocale: this.localeId
			});
		}
		
		private copy(url: Jhtml.Url) {
			Jhtml.lookupModel(url).then((model: Jhtml.Model) => {
				this.replace(model.snippet);
			});
		}
		
		private replace(snippet: Jhtml.Snippet) {
			let newFieldJq = $(snippet.elements).children();
			this.fieldJq.replaceWith(newFieldJq);
			snippet.elements = newFieldJq.toArray();
			this.fieldJq = newFieldJq;
			snippet.markAttached();
		}
		
		private drawCopyUlJq(): JQuery {
			this.copyControlJq = $("<div></div>", { class: "rocket-impl-translation-copy-control" });
			this.jqElem.prepend(this.copyControlJq);
			
			let buttonJq = $("<button />", { "type": "button", "class": "btn btn-secondary" })
					.append($("<i></i>", { class: "fa fa-copy" }));
			let copyUlJq = $("<ul></ul>").hide();
			
			buttonJq.click(() => { copyUlJq.toggle() });
			
			this.copyControlJq.append(buttonJq);
			this.copyControlJq.append(copyUlJq);
			
			if (!this.active) {
				this.copyControlJq.hide();
			}
			
			return copyUlJq;
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
}