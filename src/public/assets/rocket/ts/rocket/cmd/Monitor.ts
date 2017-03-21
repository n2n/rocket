namespace rocket.cmd {

	export class Monitor {
		private jqContainer: JQuery;
		
		constructor(jqContainer: JQuery) {
			this.jqContainer = jqContainer;
		}
		
		public asdf() {
			alert("huuii");
		}
	}
}