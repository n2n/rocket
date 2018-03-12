namespace Rocket.Impl.Translation {

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

	interface UrlDef {
		label: string,
		copyUrl: Jhtml.Url
	}

	class TranslatedContent {
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
			this._fieldJq = elemJq.children("div");
			

			this.elemJq.hide();
			this._visible = false;
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
		private toggler: Display.Toggler;

		constructor(private translatedContent: TranslatedContent) {

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
				toN2nLocale: this.translatedContent.localeId,
				toPid: this.translatedContent.pid
			});
		}

		private loaderJq: JQuery;

		private copy(url: Jhtml.Url) {
			if (this.loaderJq) return;

			this.loaderJq = $("<div />", {
				class: "rocket-load-blocker"
			}).append($("<div></div>", { class: "rocket-loading" })).appendTo(this.translatedContent.jQuery);

			Jhtml.lookupModel(url).then((result) => {
				this.replace(result.model.snippet);
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