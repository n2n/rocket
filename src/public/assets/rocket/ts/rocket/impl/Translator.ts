namespace Rocket.Impl {
	
	
	export class Translator {
		
		constructor(private container: Rocket.Cmd.Container) {
		}
		
		scan() {
			for (let context of this.container.getAllContexts()) {
				let elems: Array<HTMLElement> = context.jQuery.find(".rocket-impl-translation-manager").toArray();
				let elem;
				while (elem = elems.pop()) {
					this.initTm($(elem));
				}
					
				context.jQuery.find(".rocket-impl-translatable").each((i, elem) => {
					Translatable.from($(elem));
				});
			}
		}
		
		private initTm(jqElem: JQuery) {
			TranslationManager.from(jqElem);
		}
		
	}
	
	export class TranslationManager {
		private jqMenu: JQuery;
		
		constructor(private jqElem: JQuery) {
			this.initControl();
			this.initMenu();
		}
		
		private initControl() {
			let jqLabel = this.jqElem.children("label:first");
			let cmdList = Rocket.Display.CommandList.create(true);
			cmdList.createJqCommandButton({
				iconType: "fa fa-language",
				label: jqLabel.text(),
				tooltip: this.jqElem.data("rocket-impl-tooltip")
			}).click(() => this.toggle());
			
			jqLabel.replaceWith(cmdList.jQuery);
		}
		
		private initMenu() {
			this.jqMenu = this.jqElem.find(".rocket-impl-translation-menu");
			this.jqMenu.hide();
		}
		
		private toggle() {
			this.jqMenu.toggle();
		}
			
		static from(jqElem: JQuery): TranslationManager {
			let tm = jqElem.data("rocketImplTranslationManager");
			if (tm instanceof TranslationManager) {
				return tm;
			}
			
			tm = new TranslationManager(jqElem);
			jqElem.data("rocketImplTranslationManager", tm);
			
			return tm;
		}
		
	}
	
	export class Translatable {
		private _contents: { [localeId: string]: TranslatedContent }
		
		constructor(private jqElem: JQuery) {
		}
		
		get localeIds(): Array<string> {
			return Object.keys(this._contents);
		}
		
		get contents(): Array<TranslatedContent> {
			let O: any = Object;
			return O.values(this._contents);
		}
		
		public scan() {
			this.jqElem.contents().each((i, elem) => {
				let jqElem: JQuery = $(elem);
				let localeId = jqElem.data("rocket-impl-locale-id");
				if (!localeId || this._contents[localeId]) return;
				
				this._contents[localeId] = new TranslatedContent(localeId, jqElem);
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
	
	class TranslatedContent {
		
		constructor(private _localeId: string, private jqElem: JQuery) {
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
	}
}