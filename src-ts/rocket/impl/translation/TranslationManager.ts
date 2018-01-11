namespace Rocket.Impl.Translation {
	
	export class TranslationManager {
		private min: number = 0;
		private menuJq: JQuery;
		private translatables: Array<Translatable> = [];
		private menuItems: Array<MenuItem> = [];
		private buttonJq: JQuery = null;
		
		constructor(private jqElem: JQuery) {
			this.min = parseInt(jqElem.data("rocket-impl-min"));
			
			Toggler.simple(this.initControl(), this.initMenu());
			
			this.val();
		}
		
		private val(): Array<string> {
			let activeLocaleIds: Array<string> = [];
			
			for (let menuItem of this.menuItems) {
				if (!menuItem.active) continue;
				
				activeLocaleIds.push(menuItem.localeId);
			}
			
			let activeDisabled = activeLocaleIds.length <= this.min;
			
			for (let menuItem of this.menuItems) {
				if (menuItem.mandatory) continue;
				
				if (!menuItem.active && activeLocaleIds.length < this.min) {
					menuItem.active = true;
					activeLocaleIds.push(menuItem.localeId);
				}
				
				menuItem.disabled = activeDisabled && menuItem.active;
			}
						
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
			
			if (!changed) {
				this.changing = false;
				return;
			} 
			
			localeIds = this.val();
			
			for (let translatable of this.translatables) {
				translatable.activeLocaleIds = localeIds;
			}
			
			this.changing = false;
		}
		
		private menuChanged() {
			if (this.changing) return;
			this.changing = true;
			
			let localeIds = this.val();
			
			for (let translatable of this.translatables) {
				translatable.activeLocaleIds = localeIds;
			}
			
			this.changing = false;
		}
		
		
		private initControl(): JQuery {
			let jqLabel = this.jqElem.children("label:first");
			let cmdList = Rocket.Display.CommandList.create(true);
			let buttonJq = cmdList.createJqCommandButton({
				iconType: "fa fa-language",
				label: jqLabel.text(),
				tooltip: this.jqElem.find("rocket-impl-tooltip").text()
			});
			
			jqLabel.replaceWith(cmdList.jQuery);
			
			return buttonJq;
		}
		
		private initMenu(): JQuery {
			let menuJq = this.jqElem.find(".rocket-impl-translation-menu");
			menuJq.hide();

			menuJq.find("li").each((i, elem) => {
				let mi = new MenuItem($(elem));
				this.menuItems.push(mi);
				
				mi.whenChanged(() => {
					this.menuChanged();
				});
			});
			
			return menuJq;
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
		private _localeId: string;
		private _mandatory: boolean;
		private jqCheck: JQuery;
		private jqI: JQuery;
		private _disabled: boolean = false;
		
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
				this.disabled = true;
			}
			
			this.jqCheck.change(() => { this.updateClasses() });
		}
		
		private updateClasses() {
			if (this.disabled) {
				this.jqElem.addClass("rocket-disabled");
			} else {
				this.jqElem.removeClass("rocket-disabled");
			}
			
			if (this.active) {
				this.jqElem.addClass("rocket-active");
			} else {
				this.jqElem.removeClass("rocket-active");
			}
		}
		
		whenChanged(callback: () => any) {
			this.jqCheck.change(callback);
		}
		
		get disabled(): boolean {
			return this.jqCheck.is(":disabled") || this._disabled;
		}
		
		set disabled(disabled: boolean) {
			this._disabled = disabled;
			this.updateClasses();	
		}
		
		get active(): boolean {
			return this.jqCheck.is(":checked");
		}
		
		set active(active: boolean) {
			this.jqCheck.prop("checked", active);
			this.updateClasses();	
		}
		
		get localeId(): string {
			return this._localeId;
		}
		
		get mandatory(): boolean {
			return this._mandatory;
		}
	}
}