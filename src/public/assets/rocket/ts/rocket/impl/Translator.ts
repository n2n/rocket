namespace Rocket.Impl {
	
	export class Translator {
		
		constructor(private container: Rocket.Cmd.Container) {
		}
		
		scan() {
			for (let context of this.container.getAllContexts()) {
				let elems: Array<HTMLElement> = context.jQuery.find(".rocket-impl-translation-manager").toArray();
				let elem;
				while (elem = elems.pop()) {
					this.initTm($(elem), context);
				}
					
				let jqViewControl = $("<div />", { "class": "rocket-impl-translation-view-control" });
				context.menu.toolbar.getJqControls().append(jqViewControl);
				
				new Rocket.Display.CommandList(jqViewControl).createJqCommandButton({
					iconType: "fa fa-cog",
					label: "Languages"
				});
				
//				context.jQuery.find(".rocket-impl-translatable").each((i, elem) => {
//					Translatable.from($(elem));
//				});
			}
		}
		
		private initTm(jqElem: JQuery, context: Rocket.Cmd.Context) {
			let tm = TranslationManager.from(jqElem);
			
			let se = Rocket.Display.StructureElement.findFrom(jqElem);
			
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
	
	export class TranslationManager {
		private min: number = 0;
		private jqMenu: JQuery;
		private translatables: Array<Translatable> = [];
		private menuItems: Array<MenuItem> = [];
		
		constructor(private jqElem: JQuery) {
			this.min = parseInt(jqElem.data("rocket-impl-min"));
			
			this.initControl();
			this.initMenu();
			
			this.val();
		}
		
		private val(): Array<string> {
			let activeLocaleIds: Array<string> = [];
			
			for (let menuItem of this.menuItems) {
				if (!menuItem.active) continue;
				
				activeLocaleIds.push(menuItem.localeId);
			}
			
			if (activeLocaleIds.length >= this.min) {
				return activeLocaleIds;
			}
			
			for (let menuItem of this.menuItems) {
				if (menuItem.active) continue;
				
				activeLocaleIds.push(menuItem.localeId);
				
				if (activeLocaleIds.length >= this.min) {
					break;
				}
			}
			
			console.log(activeLocaleIds);
			
			return activeLocaleIds;
		}
		
		registerTranslatable(translatable: Translatable) {
			if (-1 < this.translatables.indexOf(translatable)) return;	
			
			this.translatables.push(translatable);
			
			translatable.activeLocaleIds = this.activeLocaleIds;
			
			translatable.jQuery.on("remove", () => this.unregisterTranslatable(translatable));
			
			for (let tc of translatable.contents) {
				tc.whenChanged(() => {
					this.activeLocaleIds = translatable.activeLocaleIds;
				});
			}
		}
		
		unregisterTranslatable(translatable: Translatable) {
			let i = this.translatables.indexOf(translatable);
			if (i > -1) {
				this.translatables.splice(i, 1);
			}
		}
		
		private changing: boolean = false;
		
		get activeLocaleIds(): Array<string> {
			let localeIds = Array<string>();
			for (let menuItem of this.menuItems) {
				if (menuItem.active) {
					localeIds.push(menuItem.localeId);
				}
			}
			return localeIds;
		}
		
		set activeLocaleIds(localeIds: Array<string>) {
			if (this.changing) return;
			this.changing = true;
			
			let changed = false;
			
			for (let menuItem of this.menuItems) {
				if (menuItem.mandatory) continue;
				
				let active = -1 < localeIds.indexOf(menuItem.localeId);
				if (menuItem.active != active) {
					changed = true;
				}
				menuItem.active = active;
			}
			
			if (!changed) return; 
			
			localeIds = this.val();
			
			for (let translatable of this.translatables) {
				translatable.activeLocaleIds = localeIds;
			}
			
			this.changing = false;
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
			
			this.jqMenu.children().each((i, elem) => {
				this.menuItems.push(new MenuItem($(elem)));
			});
			
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
	
	class MenuItem {
		private _localeId;
		private _mandatory: boolean;
		private jqCheck: JQuery;
		
		constructor(private jqElem: JQuery) {
			this._localeId = this.jqElem.data("rocket-impl-locale-id");
			this._mandatory = this.jqElem.data("rocket-impl-mandatory") ? true : false;
			
			this.init();
		}
		
		private init() {
			if (this.jqCheck) {
				throw new Error("already initialized");
			}
			
			this.jqCheck = this.jqElem.find("input[type=checkbox]");
			if (this.mandatory) {
				this.jqCheck.prop("checked", true);
				this.jqCheck.prop("disabled", true);
			}
		}
		
		get active(): boolean {
			return this.jqCheck.is(":checked");
		}
		
		set active(active: boolean) {
			this.jqCheck.prop("checked", active);	
		}
		
		get localeId(): string {
			return this._localeId;
		}
		
		get mandatory(): boolean {
			return this._mandatory;
		}
	}
	
	export class Translatable {
		private _contents: { [localeId: string]: TranslatedContent } = {}
		
		constructor(private jqElem: JQuery) {
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
		private jqTranslation: JQuery;
		private jqEnabler = null;
		public activateLabel: string = "av";
		private changedCallbacks: Array<() => any> = [];
		
		constructor(private _localeId: string, private jqElem: JQuery) {
			this.jqTranslation = jqElem.children(".rocket-impl-translation");
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
				return;
			}
			
			if (this.jqEnabler) return;
			
			this.jqEnabler = $("<button />", {
				"class": "rocket-impl-enabler",
				"type": "button",
				"text": this.activateLabel,
				"click": () => { this.active = true} 
			}).appendTo(this.jqElem);
			
			this.triggerChanged();
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