namespace rocket.display {

	export class StructureElement {
		private jqElem: JQuery;
		private group: boolean;
		private field: boolean;
		private onShowCallbacks: Array<(Group) => any> = new Array<(Group) => any>();
		private onHideCallbacks: Array<(Group) => any> = new Array<(Group) => any>();
		private toolbar: Toolbar = null;
		
		constructor(jqElem: JQuery, group: boolean, field: boolean) {
			this.jqElem = jqElem;
			this.group = group;
			this.field = field;
			
			if (group) {
				jqElem.addClass("rocket-group");
			}
			
			if (field) {
				jqElem.addClass("rocket-field");
			}
			
			jqElem.data("rocketStructureElement", this);
		}
		
		public isGroup(): boolean {
			return this.group;
		}
		
		public isField(): boolean {
			return this.field;
		}
		
		public getToolbar(): Toolbar {
			if (this.toolbar !== null) {
				return this.toolbar;
			}
			
			if (!this.group) {
				return null;
			}
			
			var jqToolbar = this.jqElem.children(".rocket-group-toolbar:first");
			if (jqToolbar.length == 0) {
				jqToolbar = $("<div />", { "class": "rocket-group-toolbar" });
				this.jqElem.prepend(jqToolbar);
			}
			
			return this.toolbar =  new Toolbar(jqToolbar);
		}
		
		public getTitle() {
			return this.jqElem.children("label:first").text();
		}
		
		public show() {
			this.jqElem.show();
			
			for (var i in this.onShowCallbacks) {
				this.onShowCallbacks[i](this);
			}
		}
		
		public hide() {
			this.jqElem.hide();
			
			for (var i in this.onHideCallbacks) {
				this.onHideCallbacks[i](this);
			}
		}
		
		public addChild(structureElement: StructureElement) {
			var that = this;
			structureElement.onShow(function () {
				that.show();
			});
		}
		
		public onShow(callback: (Group) => any) {
			this.onShowCallbacks.push(callback);
		}
		
		public onHide(callback: (Group) => any) {
			this.onHideCallbacks.push(callback);
		}
		
		public scrollTo() {
			var top = this.jqElem.offset().top;
			var maxOffset = top - 50;
			
			var height = this.jqElem.outerHeight();
			var margin = $(window).height() - height;
			
			var offset = top - (margin / 2);
			
			if (maxOffset < offset) {
				offset = maxOffset;
			}
			
			$("html, body").animate({
		    	"scrollTop": offset
		    }, 250);
		}
		
		public highlight() {
			this.jqElem.addClass("rocket-highlighted");
		}
		
		public unhighlight(slow: boolean = false) {
			this.jqElem.removeClass("rocket-highlighted");
			
			if (slow) {
				this.jqElem.addClass("rocket-highlight-remember");	
			} else {
				this.jqElem.removeClass("rocket-highlight-remember");
			}
		}

		public static from(jqElem: JQuery, createAsGroup: boolean = false, createAsField: boolean = false): StructureElement {
			var structureElement = jqElem.data("rocketStructureElement");
			if (structureElement instanceof StructureElement) return structureElement;
		
			if (!createAsGroup && !createAsField) return null;
			
			structureElement = new StructureElement(jqElem, createAsGroup, createAsField);
			jqElem.data("rocketStructureElement", structureElement);
			return structureElement;
		}
		
		public static findFrom(jqElem: JQuery): StructureElement {
			jqElem = jqElem.parents(".rocket-group, .rocket-field");
			
			var structureElement = jqElem.data("rocketStructureElement");
			if (structureElement instanceof StructureElement) {
				return structureElement;
			}
			
			return null;
		}
	}
	
	class Toolbar {
		private jqToolbar: JQuery;
		private jqControls: JQuery;
		private jqCommands: JQuery;
		
		constructor(jqToolbar: JQuery) {
			this.jqToolbar = jqToolbar;
			
			this.jqControls = jqToolbar.children(".rocket-group-controls");
			if (this.jqControls.length == 0) {
				this.jqControls = $("<div />", { "class": "rocket-group-controls"});
				this.jqToolbar.append(this.jqControls);
				this.jqControls.hide();
			} else if (this.jqControls.is(':empty')) {
				this.jqControls.hide();
			}
			
			this.jqCommands = jqToolbar.children(".rocket-simple-commands");
			if (this.jqCommands.length == 0) {
				this.jqCommands = $("<div />", { "class": "rocket-simple-commands"});
				this.jqToolbar.append(this.jqCommands);
				this.jqCommands.hide();
			} else if (this.jqCommands.is(':empty')) {
				this.jqCommands.hide();
			}
			
			if (this.jqControls.is(':empty') && this.jqCommands.is(':empty')) {
				this.jqToolbar.hide();
			}
		}
		
		public show() {
			this.jqToolbar.show();
		}
		
		public hide() {
			this.jqToolbar.hide();
		}
		
		public getJqControls(): JQuery {
			return this.jqControls;	
		}
		
		public getJqCommands(): JQuery {
			return this.jqCommands;
		}
		
		public createCommandButton(iconType: string, label: string, type: string, tooltip: string = null): JQuery {
			this.show();
			this.jqCommands.show();
			
			var jqButton = $("<button />", { 
				"class": "btn btn-" + type,
				"title": tooltip
			}).append($("<i />", {
				"class": iconType
			})).append($("<span />", {
				"text": label
			}));
			
			this.jqCommands.append(jqButton);
			
			return jqButton;
		}
	}
}