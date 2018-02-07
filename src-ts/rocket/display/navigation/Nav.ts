namespace Rocket.Display {
	export class Nav {
		private _elemJq: JQuery;
		private _state: Rocket.Impl.NavState;

		public init(elemJq: JQuery) {
			this.elemJq = elemJq;
		}

		public scrollToPos(scrollPos: number) {
			this.elemJq.animate({
				scrollTop: scrollPos
			}, 0);
		}

		get state(): Rocket.Impl.NavState {
			return this._state;
		}

		set state(value: Rocket.Impl.NavState) {
			this._state = value;
		}

		get elemJq(): JQuery {
			return this._elemJq;
		}

		set elemJq(value: JQuery) {
			this._elemJq = value;
		}
	}
}