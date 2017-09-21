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
			
			var jqSelectors = this.jqElem.find(".rocket-entry-selector:first");
			if (jqSelectors.length > 0) {
				this._selector = new EntrySelector(jqSelectors.first(), this);
			}
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
		
		private static from(jqElem: JQuery): Entry {
			var entry = jqElem.data("rocketEntry");
			if (entry instanceof Entry) {
				return entry;
			}
			
			entry = new Entry(jqElem); 
			jqElem.data("rocketEntry", entry);
			
			return entry;
		}
		
		static findFrom(jqElem: JQuery): Entry {
			var jqElem = jqElem.closest(".rocket-entry");
			
			if (jqElem.length == 0) return null;
			
			return Entry.from(jqElem);
		}

		static findAll(jqElem: JQuery, includeSelf: boolean = false): Array<Entry> {
			var entries = new Array<Entry>();
			
			var jqEntries = jqElem.find(".rocket-entry");
			
			jqEntries = jqEntries.add(jqElem.filter(".rocket-entry"));
			
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