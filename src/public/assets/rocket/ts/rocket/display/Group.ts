namespace Rocket.Display {

	export class StructureElement {
		private jqElem: JQuery;
		private onShowCallbacks: Array<(se: StructureElement) => any> = [];
		private onHideCallbacks: Array<(se: StructureElement) => any> = [];
		private toolbar: Toolbar = null;
		
		constructor(jqElem: JQuery) {
			this.jqElem = jqElem;
			
			jqElem.addClass("rocket-structure-element");
			jqElem.data("rocketStructureElement", this);
			
			this.valClasses();
		}
		
		private valClasses() {
			if (this.isField() || this.isGroup()) {
				this.jqElem.removeClass("rocket-structure-element");
			} else {
				this.jqElem.addClass("rocket-structure-element");
			}
		}
		
		get jQuery(): JQuery {
			return this.jqElem;
		}
		
		public setGroup(group: boolean) {
			if (!group) {
				this.jqElem.removeClass("rocket-group");
			} else {
				this.jqElem.addClass("rocket-group");
			}
			
			this.valClasses();
		}
		
		public isGroup(): boolean {
			return this.jqElem.hasClass("rocket-group");
		}
		
		public setField(field: boolean) {
			if (!field) {
				this.jqElem.removeClass("rocket-field");
			} else {
				this.jqElem.addClass("rocket-field");
			}
			
			this.valClasses();
		}
		
		public isField(): boolean {
			return this.jqElem.hasClass("rocket-field");
		}
		
		public getToolbar(): Toolbar {
			if (this.toolbar !== null) {
				return this.toolbar;
			}
			
			if (!this.isGroup()) {
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
		
		public getParent(): StructureElement {
			return StructureElement.findFrom(this.jqElem);
		}
		
		public isVisible() {
			return this.jqElem.is(":visible");
		}
		
		public show(includeParents: boolean = false) {
			for (var i in this.onShowCallbacks) {
				this.onShowCallbacks[i](this);
			}
			
			this.jqElem.show();
			
			var parent;
			if (includeParents && null !== (parent = this.getParent())) {
				parent.show(true)
			}
		}
		
		public hide() {
			for (var i in this.onHideCallbacks) {
				this.onHideCallbacks[i](this);
			}
			
			this.jqElem.hide();
		}
		
//		public addChild(structureElement: StructureElement) {
//			var that = this;
//			structureElement.onShow(function () {
//				that.show();
//			});
//		}
		
		public onShow(callback: (group: StructureElement) => any) {
			this.onShowCallbacks.push(callback);
		}
		
		public onHide(callback: (group: StructureElement) => any) {
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
		
		private highlightedParent: StructureElement = null;
		
		public highlight(findVisibleParent: boolean = false) {
			this.jqElem.addClass("rocket-highlighted");
			
			if (!findVisibleParent || this.isVisible()) return;
				
			this.highlightedParent = this;
			while (null !== (this.highlightedParent = this.highlightedParent.getParent())) {
				if (!this.highlightedParent.isVisible()) continue;
				
				this.highlightedParent.highlight();
				return;
			}
		}
		
		public unhighlight(slow: boolean = false) {
			this.jqElem.removeClass("rocket-highlighted");
			
			if (slow) {
				this.jqElem.addClass("rocket-highlight-remember");	
			} else {
				this.jqElem.removeClass("rocket-highlight-remember");
			}
			
			if (this.highlightedParent !== null) {
				this.highlightedParent.unhighlight();
				this.highlightedParent = null;
			}
		}

		public static from(jqElem: JQuery, create: boolean = false): StructureElement {
			var structureElement = jqElem.data("rocketStructureElement");
			if (structureElement instanceof StructureElement) return structureElement;
		
			if (!create) return null;
			
			structureElement = new StructureElement(jqElem);
			jqElem.data("rocketStructureElement", structureElement);
			return structureElement;
		}
		
		public static findFrom(jqElem: JQuery): StructureElement {
			jqElem = jqElem.closest(".rocket-structure-element, .rocket-group, .rocket-field");
			
			if (jqElem.length == 0) return null;
			
			var structureElement = jqElem.data("rocketStructureElement");
			if (structureElement instanceof StructureElement) {
				return structureElement;
			}
			
			structureElement = StructureElement.from(jqElem, true);
			jqElem.data("rocketStructureElement", structureElement);
			return structureElement;
		}
	}
	
	export class Toolbar {
		private jqToolbar: JQuery;
		private jqControls: JQuery;
		private commandList: CommandList;
		
		public constructor(jqToolbar: JQuery) {
			this.jqToolbar = jqToolbar;
			
			this.jqControls = jqToolbar.children(".rocket-group-controls");
			if (this.jqControls.length == 0) {
				this.jqControls = $("<div />", { "class": "rocket-group-controls"});
				this.jqToolbar.append(this.jqControls);
				this.jqControls.hide();
			} else if (this.jqControls.is(':empty')) {
				this.jqControls.hide();
			}
			
			var jqCommands = jqToolbar.children(".rocket-simple-commands");
			if (jqCommands.length == 0) {
				jqCommands = $("<div />", { "class": "rocket-simple-commands"});
				jqToolbar.append(jqCommands);
			}
			this.commandList = new CommandList(jqCommands, true);
		}
		
		get jQuery(): JQuery {
			return this.jqToolbar;
		}
		
		public getJqControls(): JQuery {
			return this.jqControls;	
		}
		
		public getCommandList(): CommandList {
			return this.commandList;
		}
	}
	
	export class CommandList {
		private jqCommandList: JQuery;
		private simple: boolean;
		
		public constructor(jqCommandList: JQuery, simple: boolean = false) {
			this.jqCommandList = jqCommandList;
			
			if (simple) {
				jqCommandList.addClass("rocket-simple-commands");
			}
		}
		
		get jQuery(): JQuery {
			return this.jqCommandList;
		}
		
		public createJqCommandButton(buttonConfig: ButtonConfig/*, iconType: string, label: string, severity: Severity = Severity.SECONDARY, tooltip: string = null*/, prepend: boolean = false): JQuery {
			this.jqCommandList.show();
			
			if (buttonConfig.iconType === undefined) {
				buttonConfig.iconType = "fa fa-circle-o";
			}
			
			if (buttonConfig.severity === undefined) {
				buttonConfig.severity = Severity.SECONDARY;
			}
			
			var jqButton = $("<button />", { 
				"class": "btn btn-" + buttonConfig.severity,
				"title": buttonConfig.tooltip,
				"type": "button"
			}).append($("<i />", {
				"class": buttonConfig.iconType
			})).append($("<span />", {
				"text": buttonConfig.label
			}));
			
			if (prepend) {
				this.jqCommandList.prepend(jqButton);
			} else {
				this.jqCommandList.append(jqButton);
			}
			
			return jqButton;
		}
		
		static create(simple: boolean = false) {
			return new CommandList($("<div />"), simple);
		}
	}
	
	export interface ButtonConfig {
		iconType?: string;
		label: string;
		severity?: Severity;
		tooltip?: string;
	}
}