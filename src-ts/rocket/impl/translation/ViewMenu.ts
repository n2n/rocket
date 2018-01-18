namespace Rocket.Impl.Translation {

	
	export class ViewMenu {
		private translatables: Array<Translatable> = [];
		private jqStatus: JQuery;
		private menuUlJq : JQuery;
		private items: { [localeId: string]: ViewMenuItem } = {};
		private changing: boolean = false;
		
		constructor(private jqContainer: JQuery) {
			
		}
		
		private draw(languagesLabel: string, visibleLabel: string, tooltip: string) {
			$("<div />", { "class": "rocket-impl-translation-status" })
					.append($("<label />", { "text": visibleLabel }).prepend($("<i></i>", { "class": "fa fa-language" })))
					.append(this.jqStatus = $("<span></span>"))
					.prependTo(this.jqContainer);
			
			let buttonJq = new Rocket.Display.CommandList(this.jqContainer).createJqCommandButton({
				iconType: "fa fa-cog",
				label: languagesLabel,
				tooltip: tooltip
			});
			
			let menuJq = $("<div />", { "class": "rocket-impl-translation-status-menu" })
					.append(this.menuUlJq = $("<ul></ul>"))
					.append($("<div />", { "class": "rocket-impl-tooltip", "text": tooltip }))
					.hide();
			Display.Toggler.simple(buttonJq, menuJq);
			
			this.jqContainer.append(menuJq);
			
		}	
		
		
		private updateStatus() {
			let prettyLocaleIds: Array<string> = [];
			for (let localeId in this.items) {
				if (!this.items[localeId].on) continue;
				
				prettyLocaleIds.push(this.items[localeId].prettyLocaleId);
			}
			
			this.jqStatus.empty();
			this.jqStatus.text(prettyLocaleIds.join(", "));
			
			let onDisabled = prettyLocaleIds.length == 1;
			
			for (let localeId in this.items) {
				this.items[localeId].disabled = onDisabled && this.items[localeId].on;
			} 
		}
		
		get visibleLocaleIds(): Array<string> {
			let localeIds: Array<string> = [];
			
			for (let localeId in this.items) {
				if (!this.items[localeId].on) continue;
				
				localeIds.push(localeId);
			}
			
			return localeIds;
		}
		
		registerTranslatable(translatable: Translatable) {
			if (-1 < this.translatables.indexOf(translatable)) return;
			
			if (!this.jqStatus) {
				this.draw(translatable.jQuery.data("rocket-impl-languages-label"), 
						translatable.jQuery.data("rocket-impl-visible-label"), 
						translatable.jQuery.data("rocket-impl-languages-view-tooltip"));
			}
			
			this.translatables.push(translatable);
			
			translatable.jQuery.on("remove", () => this.unregisterTranslatable(translatable));
			
			for (let content of translatable.contents) {
				if (!this.items[content.localeId]) {
					let item = this.items[content.localeId] = new ViewMenuItem(content.localeId, content.localeName, content.prettyLocaleId);
					item.draw($("<li />").appendTo(this.menuUlJq));
					
					item.on = Object.keys(this.items).length == 1;
					item.whenChanged(() => this.menuChanged());
					
					this.updateStatus();
				}
				
				content.visible = this.items[content.localeId].on;
				
				content.whenChanged(() => {
					if (this.changing || !content.active) return;
					
					this.items[content.localeId].on = true;
				});
			}
		}
		
		unregisterTranslatable(translatable: Translatable) {
			let i = this.translatables.indexOf(translatable);
			
			if (-1 < i) {
				this.translatables.splice(i, 1);
			}
		}
		
		private menuChanged() {
			if (this.changing) {
				throw new Error("already changing");
			}	
			
			this.changing = true;
			
			let visiableLocaleIds: Array<string> = [];
			
			for (let i in this.items) {
				if (this.items[i].on) {
					visiableLocaleIds.push(this.items[i].localeId);
				} 
			}
			
			for (let translatable of this.translatables) {
				translatable.visibleLocaleIds = visiableLocaleIds;
			}
			
			this.updateStatus();
			this.changing = false;
		}
		static from(jqElem: JQuery): ViewMenu {
			let vm = jqElem.data("rocketImplViewMenu");
			if (vm instanceof ViewMenu) {
				return vm;
			}
			
			vm = new ViewMenu(jqElem);
			jqElem.data("rocketImplViewMenu", vm);
			
			return vm;
		}
	}
	
	class ViewMenuItem {
		private _on: boolean = true;
		private changedCallbacks: Array<() => any> = [];
		private jqA: JQuery;
		private jqI: JQuery;
		
		constructor (public localeId: string, public label: string, public prettyLocaleId: string) {
		}
		
		draw(jqElem: JQuery) {
			this.jqI = $("<i></i>");
			
			this.jqA = $("<a />", { "href": "", "text": this.label + " ", "class": "btn" })
					.append(this.jqI)
					.appendTo(jqElem)
					.click((evt: any) => {
						if (this.disabled) return;
						
						this.on = !this.on;
						
						evt.preventDefault();
						return false;
					});
			
			this.checkI();
		}
		
		get disabled(): boolean {
			return this.jqA.hasClass("disabled");
		}
		
		set disabled(disabled: boolean) {
			if (disabled) {
				this.jqA.addClass("disabled");
			} else {
				this.jqA.removeClass("disabled");
			}
		}
		
		get on(): boolean {
			return this._on;
		}
		
		set on(on: boolean) {
			if (this._on == on) return;
			
			this._on = on;
			this.checkI();
			
			this.triggerChanged();
		}
		
		private triggerChanged() {
			for (let callback of this.changedCallbacks) {
				callback();
			}
		}
		
		whenChanged(callback: () => any) {
			this.changedCallbacks.push(callback);
		}
		
		private checkI() {
			if (this.on) {
				this.jqA.addClass("rocket-active");
				this.jqI.attr("class", "fa fa-toggle-on");
			} else {
				this.jqA.removeClass("rocket-active");
				this.jqI.attr("class", "fa fa-toggle-off");
			}
		}
	}
}