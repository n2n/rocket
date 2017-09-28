namespace Rocket.Impl {
	
	
	export class Translator {
		
		static scan(contexts: Array<Rocket.Cmd.Context>) {
			for (let context of contexts) {
				context.jQuery.find(".rocket-impl-translatable").each((i, elem) => {
					Translatable.from($(elem));
				});
			}
		}
	}
	
	export class TranslationManager {
		
		static findFrom(jqElem: JQuery) {
			jqElem.find
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