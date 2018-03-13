namespace Rocket.Impl.Translation {

	export class Translatable {
		private srcGuiIdPath: string|null = null;
		private loadUrlDefs: { [localeId: string]: UrlDef } = {};
		private copyUrlDefs: { [localeId: string]: UrlDef } = {};
		private _contents: { [localeId: string]: TranslatedContent } = {}

		constructor(private jqElem: JQuery) {
			let srcLoadConfig = jqElem.data("rocket-impl-src-load-config");
			
			if (!srcLoadConfig) return;
			
			this.srcGuiIdPath = srcLoadConfig.guiIdPath;
			for (let localeId in srcLoadConfig.loadUrlDefs) {
				this.loadUrlDefs[localeId] = {
					label: srcLoadConfig.loadUrlDefs[localeId].label,
					url: Jhtml.Url.create(srcLoadConfig.loadUrlDefs[localeId].url)
				};
			}
			for (let localeId in srcLoadConfig.copyUrlDefs) {
				this.copyUrlDefs[localeId] = {
					label: srcLoadConfig.copyUrlDefs[localeId].label,
					url: Jhtml.Url.create(srcLoadConfig.copyUrlDefs[localeId].url)
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
		
		get loadJobs(): LoadJob[] {
			if (!this.srcGuiIdPath) return [];
			
			let loadJobs: LoadJob[] = [];
			for (let content of this.contents) {
				if (content.loaded || content.loading || !content.visible || !content.active
						|| !this.loadUrlDefs[content.localeId]) {
					continue;
				}

				loadJobs.push({
					url: this.loadUrlDefs[content.localeId].url.extR(null, { "propertyPath": content.propertyPath }),
					guiIdPath: this.srcGuiIdPath,
					content: content
				});
			}
			return loadJobs;
		}

		public scan() {
			this.jqElem.children().each((i, elem) => {
				let jqElem: JQuery = $(elem);
				let localeId = jqElem.data("rocket-impl-locale-id");
				if (!localeId || this._contents[localeId]) return;

				let tc = this._contents[localeId] = new TranslatedContent(localeId, jqElem);
				tc.drawCopyControl(this.copyUrlDefs, this.srcGuiIdPath);
			});
		}

		static test(elemJq: JQuery): Translatable {
			let translatable = elemJq.data("rocketImplTranslatable");
			if (translatable instanceof Translatable) {
				return translatable;
			}

			return null;
		}

		static from(jqElem: JQuery): Translatable {
			let translatable = Translatable.test(jqElem);
			if (translatable instanceof Translatable) {
				return translatable;
			}

			translatable = new Translatable(jqElem);
			jqElem.data("rocketImplTranslatable", translatable);
			translatable.scan();
			return translatable;
		}
	}

	export interface LoadJob {
		url: Jhtml.Url;
		guiIdPath: string;
		content: TranslatedContent
	}
	
	interface UrlDef {
		label: string,
		url: Jhtml.Url
	}

	export class TranslatedContent {
//		private jqTranslation: JQuery;
		private _propertyPath: string;
		private _pid: string;
		private _fieldJq: JQuery;
		private jqEnabler: JQuery = null;
		private copyControlJq: JQuery = null;
		private changedCallbacks: Array<() => any> = [];
		private _visible: boolean = true;

		constructor(private _localeId: string, private elemJq: JQuery) {
			Display.StructureElement.from(elemJq, true);
//			this.jqTranslation = jqElem.children(".rocket-impl-translation");
			this._propertyPath = elemJq.data("rocket-impl-property-path");
			this._pid = elemJq.data("rocket-impl-ei-id") || null;
			this._fieldJq = elemJq.children();
			
			this.elemJq.hide();
			this._visible = false;
		}
		
		get loaded() {
			return this.elemJq.children("div").children("div")
					.children("input[type=hidden].rocket-impl-unloaded").length == 0;
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

		get pid(): string|null {
			return this._pid;
		}

		get prettyLocaleId(): string {
			return this.elemJq.data("rocket-impl-pretty-locale");
		}

		get localeName(): string {
			return this.elemJq.data("rocket-impl-locale-name");
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

				this.elemJq.removeClass("rocket-inactive");
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

			this.elemJq.addClass("rocket-inactive");
		}

		private copyControl: CopyControl;

		drawCopyControl(copyUrlDefs: { [localeId: string]: UrlDef }, guiIdPath: string) {
			for (let localeId in copyUrlDefs) {
				if (localeId == this.localeId) continue;

				if (!this.copyControl) {
					this.copyControl = new CopyControl(this, guiIdPath);
					this.copyControl.draw(this.elemJq.data("rocket-impl-copy-tooltip"));
				}

				this.copyControl.addUrlDef(copyUrlDefs[localeId]);
			}
		}
		
		private loaderJq: JQuery;
		
		get loading(): boolean {
			return !!this.loaderJq;
		}
		
		set loading(loading: boolean) {
			if (!loading) {
				if (!this.loaderJq) return;
				
				this.loaderJq.remove();
				this.loaderJq = null;
				return;
			}
			
			if (this.loaderJq) return;
			
			this.loaderJq = $("<div />", {
				class: "rocket-load-blocker"
			}).append($("<div></div>", { class: "rocket-loading" })).appendTo(this.elemJq);
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
		private toggler: Display.Toggler;

		constructor(private translatedContent: TranslatedContent, private guiIdPath: string) {

		}

		draw(tooltip: string) {
			this.elemJq = $("<div></div>", { class: "rocket-impl-translation-copy-control rocket-simple-commands" });
			this.translatedContent.jQuery.append(this.elemJq);

			let buttonJq = $("<button />", { "type": "button", "class": "btn btn-secondary" })
					.append($("<i></i>", { class: "fa fa-copy", title: tooltip }));
			let menuJq = $("<div />", { class: "rocket-impl-translation-copy-menu" })
					.append(this.menuUlJq = $("<ul></ul>"))
					.append($("<div />", { class: "rocket-impl-tooltip", text: tooltip }));

			this.toggler = Display.Toggler.simple(buttonJq, menuJq);

			this.elemJq.append(buttonJq);
			this.elemJq.append(menuJq);

//			if (!this.translatedContent.active) {
//				this.hide();
//			}
		}

		addUrlDef(urlDef: UrlDef) {
			let url = this.completeCopyUrl(urlDef.url);
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
				toN2nLocale: this.translatedContent.localeId,
				toPid: this.translatedContent.pid
			});
		}


		private copy(url: Jhtml.Url) {
			if (this.translatedContent.loading) return;
			
			let lje = new LoadJobExecuter();

			lje.add({
				content: this.translatedContent,
				guiIdPath: this.guiIdPath,
				url: url
			});
			
			lje.exec();
		}

		private replace(snippet: Jhtml.Snippet) {
//			let newFieldJq = $(snippet.elements).children();
//			this.translatedContent.replaceField(newFieldJq);
//			snippet.elements = newFieldJq.toArray();
//			snippet.markAttached();
//
//			this.loaderJq.remove();
//			this.loaderJq = null;
		}
	}
}