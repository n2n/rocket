namespace Rocket.Impl.Order {

	export class Control {
		private collection: Display.Collection;
		
		constructor(private elemJq: JQuery) {
			this.collection = Display.Collection.of(this.elemJq);
			if (!this.collection) return;
			
			this.collection.setupSortable();	
		}
		
		get jQuery(): JQuery {
			return this.elemJq;
		}
	}
}