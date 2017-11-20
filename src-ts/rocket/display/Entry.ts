namespace Rocket.Display {
	
	export class Entry {
		private _selector: EntrySelector = null;
		private _state: Entry.State = Entry.State.PERSISTENT;
		private callbackRegistery: util.CallbackRegistry<EntryCallback> = new util.CallbackRegistry<EntryCallback>();
		
		constructor(private jqElem: JQuery) {
			var that = this;
			jqElem.on("remove", function () {
				that.trigger(Entry.EventType.DISPOSED);
			});
			
			let jqSelector = jqElem.find(".rocket-entry-selector:first");
			if (jqSelector.length > 0) {
				this.initSelector(jqSelector);
			}
		}
		
		private initSelector(jqSelector: JQuery) {
			this._selector = new EntrySelector(jqSelector, this);
			
			var that = this;
			this.jqElem.click(function (e) {
				if (getSelection().toString() || util.ElementUtils.isControl(e.target)) {
					return;
				}
				
				that._selector.selected = !that._selector.selected;
			});
		}
		
		private trigger(eventType: Entry.EventType) {
			var entry = this;
			this.callbackRegistery.filter(eventType.toString())
					.forEach(function (callback: EntryCallback) {
						callback(entry);
					});
		}
		
		public on(eventType: Entry.EventType, callback: EntryCallback) {
			this.callbackRegistery.register(eventType.toString(), callback);
		}
		
		public off(eventType: Entry.EventType, callback: EntryCallback) {
			this.callbackRegistery.unregister(eventType.toString(), callback);
		}
		
		get jqQuery(): JQuery {
			return this.jqElem;
		}
		
		show() {
			this.jqElem.show();
		}
		
		hide() {
			this.jqElem.hide();
		}
		
		dispose() {
			this.jqElem.remove();
		}
		
		get state(): Entry.State {
			return this._state;
		}
		
		set state(state: Entry.State) {
			if (this._state == state) return;
			
			this._state = state;
			
			if (state == Entry.State.REMOVED) {
				this.trigger(Entry.EventType.REMOVED);
			}
		}
		
		get generalId(): string {
			return this.jqElem.data("rocket-general-id").toString();		
		}
		
		get id(): string {
			if (this.draftId !== null) {
				return this.draftId.toString();
			}
			
			return this.idRep;
		}
		
		get idRep(): string {
			return this.jqElem.data("rocket-id-rep").toString();
		}
		
		get draftId(): number {
			var draftId = parseInt(this.jqElem.data("rocket-draft-id"));
			if (!isNaN(draftId)) {
				return draftId;
			}
			return null;
		}
		
		get identityString(): string {
			return this.jqElem.data("rocket-identity-string");
		}
		
		get selector(): EntrySelector {
			return this._selector;	
		}
		
		private findTreeLevelClass(): string|null {
			let cl = this.jqElem.get(0).classList;
			
			for (let i = 0; i < cl.length; i++) {
				let className = cl.item(i);
				if (className.startsWith(Entry.TREE_LEVEL_CSS_CLASS_PREFIX)) {
					return className;
				}
			}
			
			return null;
		}
		
		get treeLevel(): number|null {
			let className = this.findTreeLevelClass()
			if (className === null) return null;
			
			return parseInt(className.substr(Entry.TREE_LEVEL_CSS_CLASS_PREFIX.length));
		}
		
		set treeLevel(treeLevel: number|null) {
			let className = this.findTreeLevelClass();
			if (className) {
				this.jqElem.removeClass(className);
			} 
			
			if (treeLevel) {
				this.jqElem.addClass(Entry.TREE_LEVEL_CSS_CLASS_PREFIX + treeLevel)
			}
		}
		
		static readonly CSS_CLASS = "rocket-entry";
		static readonly TREE_LEVEL_CSS_CLASS_PREFIX = "rocket-tree-level-";
		
		private static from(elemJq: JQuery): Entry {
			var entry = elemJq.data("rocketEntry");
			if (entry instanceof Entry) {
				return entry;
			}
			
			entry = new Entry(elemJq); 
			elemJq.data("rocketEntry", entry);
			elemJq.addClass(Entry.CSS_CLASS);
			
			return entry;
		}
		
		static of(jqElem: JQuery): Entry {
			var jqElem = jqElem.closest("." + Entry.CSS_CLASS);
			
			if (jqElem.length == 0) return null;
			
			return Entry.from(jqElem);
		}

		static find(jqElem: JQuery, includeSelf: boolean = false): Entry|null {
			let entries = Entry.findAll(jqElem, includeSelf);
			if (entries.length > 0) {
				return entries[0]
			}
			return null;
		}
		
		static findAll(jqElem: JQuery, includeSelf: boolean = false): Array<Entry> {
			var entries = new Array<Entry>();
			
			var jqEntries = jqElem.find("." + Entry.CSS_CLASS);
			
			jqEntries = jqEntries.add(jqElem.filter("." + Entry.CSS_CLASS));
			
			jqEntries.each(function () {
				entries.push(Entry.from($(this)));
			});
			
			return entries;
		}
	}
	
	export interface EntryCallback {
		(entry: Entry): any;
	}
	
	export namespace Entry {
		export enum State {
			PERSISTENT /*= "persistent"*/,
			REMOVED /*= "removed"*/
		}
		
		export enum EventType {
			DISPOSED /*= "disposed"*/,
			REFRESHED /*= "refreshed"*/,
			REMOVED /*= "removed"*/
		}
	}
}