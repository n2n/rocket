namespace rocket.impl.overview {
	import cmd = rocket.cmd;
	import impl = rocket.impl.overview;
	
	var $ = jQuery;

	export class Header {
		private jqElem: JQuery;
		private state: State;
		private quicksearchForm: QuicksearchForm;
		private critmodSelect: CritmodSelect;
		private critmodForm: CritmodForm;
		
		constructor(private overviewContent: OverviewContent) {
			
		}
		
		init(jqElem: JQuery) {
			this.jqElem = jqElem;
			
			this.state = new State(this.overviewContent);
			this.state.draw(this.jqElem.find(".rocket-impl-state:first"));
			
			this.quicksearchForm = new QuicksearchForm(this.overviewContent);
			this.quicksearchForm.init(this.jqElem.find("form.rocket-impl-quicksearch:first"));
			
			this.critmodSelect = new CritmodSelect(this.overviewContent);
			this.critmodSelect.init(this.jqElem.find("form.rocket-impl-critmod-select:first"));
			
			this.critmodForm = new CritmodForm(this.overviewContent);
			this.critmodForm.init(this.jqElem.find("form.rocket-impl-critmod:first"));
		}
	}
	
	class State {
		private jqElem: JQuery;
		private jqAllButton: JQuery;
		private jqSelectedButton: JQuery;
		
		constructor(private overviewContent: OverviewContent) {
		}
		
		public draw(jqElem: JQuery) {
			this.jqElem = jqElem;
			var that = this;
			
			this.jqAllButton = $("<button />", { "type": "button", "class": "btn btn-secondary" }).appendTo(jqElem);
			this.jqAllButton.click(function () {
				that.overviewContent.showAll();
				that.reDraw();
			});
			
			this.jqSelectedButton = $("<button />", { "type": "button", "class": "btn btn-secondary" }).appendTo(jqElem);
			this.jqSelectedButton.click(function () {
				that.overviewContent.showSelected();
				that.reDraw();
			});
			
			this.reDraw();
			
			this.overviewContent.whenContentChanged(function () { that.reDraw(); });
			this.overviewContent.whenSelectionChanged(function () { that.reDraw(); }); 
		}
		
		public reDraw() {
			var numEntries = this.overviewContent.numEntries;
			if (numEntries == 1) {
				this.jqAllButton.text(numEntries + " " + this.jqElem.data("entries-label"));
			} else {
				this.jqAllButton.text(numEntries + " " + this.jqElem.data("entries-plural-label"));
			}
			
			if (this.overviewContent.selectedOnly) {
				this.jqAllButton.removeClass("active");
				this.jqSelectedButton.addClass("active");
			} else {
				this.jqAllButton.addClass("active");
				this.jqSelectedButton.removeClass("active");
			}
			
			
			if (!this.overviewContent.selectable) {
				this.jqSelectedButton.hide();
				return;
			}
			
			this.jqSelectedButton.show();
			
			var numSelected = this.overviewContent.numSelectedEntries;
			if (numSelected == 1) {
				this.jqSelectedButton.text(numSelected + " " + this.jqElem.data("selected-label"));
			} else {
				this.jqSelectedButton.text(numSelected + " " + this.jqElem.data("selected-plural-label"));
			}
			
			if (0 == numSelected) {
				this.jqSelectedButton.prop("disabled", true);
				return;
			}
			
			this.jqSelectedButton.prop("disabled", false);			
		}
	}
	
	class QuicksearchForm {
		private jqSearchButton: JQuery;
		private jqSearchInput: JQuery;
		private form: Form;
		
		constructor(private overviewContent: OverviewContent) {
		}
		
		public init(jqForm: JQuery) {
			if (this.form) {
				throw new Error("Quicksearch already initialized.");
			}
			
			this.form = Form.from(jqForm);
			
			var that = this;
			this.form.on(Form.EventType.SUBMIT, function () {
				that.onSubmit();
			});
			this.form.config.blockContext = false;
			this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
			this.form.config.successResponseHandler = function (data: string) {
				that.whenSubmitted(data);
			}
			
			this.initListeners();
		}
		
		private initListeners() {
			this.form.reset();
			var jqButtons = this.form.jQuery.find("button[type=submit]");
			this.jqSearchButton = $(jqButtons.get(0));
			var jqClearButton = $(jqButtons.get(1));
			this.jqSearchInput = this.form.jQuery.find("input[type=search]:first");
			var that = this;
			
			this.jqSearchInput.on("paste keyup", function () {
				that.send(false);
			});
			
			this.jqSearchInput.on("change", function () {
				that.send(true);
			});
			
			jqClearButton.on("click", function () {
				that.jqSearchInput.val("");	
				that.updateState();
			});
		}
		
		private sc = 0;
		private serachVal = null;
		
		private updateState() {
			if (this.jqSearchInput.val().length > 0) {
				this.form.jQuery.addClass("rocket-active");
			} else {
				this.form.jQuery.removeClass("rocket-active");
			}
		}
		
		private send(force: boolean) {
			var searchVal = this.jqSearchInput.val();
			
			if (this.serachVal == searchVal) return;
			
			this.updateState();

			this.overviewContent.clear(true);
			
			this.serachVal = searchVal;
			
			var si = ++this.sc;
			var that = this;
			
			if (force) {
				that.jqSearchButton.click();
				return;
			}
			
			setTimeout(function () {
				if (si !== that.sc) return;
				
				that.jqSearchButton.click();
			}, 300);

		}
		
		private onSubmit() {
			this.sc++;
			this.overviewContent.clear(true);
		}
		
		private whenSubmitted(data) {
			this.overviewContent.initFromResponse(data);
		}
	}
	
	class CritmodSelect {
		private form: Form;
		
		constructor(private overviewContent: OverviewContent) {
		}
		
		public init(jqForm: JQuery) {
			if (this.form) {
				throw new Error("CritmodSelect already initialized.");
			}
			
			this.form = Form.from(jqForm);
			
			jqForm.find("button[type=submit]").hide();
			
			this.form.config.blockContext = false;
			this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
			this.form.config.autoSubmitAllowed = false;
			
			var that = this;
			this.form.config.successResponseHandler = function (data: string) {
				that.whenSubmitted(data);
			}
			
			jqForm.find("select:first").change(function () {
				that.send($(this).val());
			});
		}
		
//		private sc = 0;
//		private serachVal = null;
//		
//		private updateState() {
//			if (this.jqSearchInput.val().length > 0) {
//				this.form.jQuery.addClass("rocket-active");
//			} else {
//				this.form.jQuery.removeClass("rocket-active");
//			}
//		}
		
		private send(id: string) {
			this.form.submit();
		}
		
		private onSubmit() {
			this.overviewContent.clear(true);
		}
		
		private whenSubmitted(data) {
			this.overviewContent.initFromResponse(data);
		}
	}
	
	class CritmodForm {
		private form: Form;
		private _critmodSaveId: string = null;
		private jqSaveButtonContainer: JQuery;
		private jqDeleteButtonContainer: JQuery;
		
		constructor(private overviewContent: OverviewContent) {
		}
		
		public init(jqForm: JQuery) {
			if (this.form) {
				throw new Error("CritmodForm already initialized.");
			}
			
			this._critmodSaveId = jqForm.data("rocket-impl-critmod-save-id");
			this.form = Form.from(jqForm);
			
			this.form.config.blockContext = false;
			this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
			var that = this;
			this.form.config.successResponseHandler = function (data: string) {
				that.whenSubmitted(data);
			};
			this.form.on(Form.EventType.SUBMIT, function () {
				that.onSubmit();
			});
			
			this.jqSaveButtonContainer = jqForm.find(".rocket-impl-critmod-save").parent();
			this.jqDeleteButtonContainer = jqForm.find(".rocket-impl-critmod-delete").parent();
			
			jqForm.find("select:first").change(function () {
				that.send($(this).val());
			});
			
			this.updateState();
		}
		
		get critmodSaveId(): string {
			return this._critmodSaveId;
		}
		
		private updateState() {
			if (this.critmodSaveId) {
				this.jqSaveButtonContainer.show();
				this.jqDeleteButtonContainer.show();
			} else {
				this.jqSaveButtonContainer.hide();
				this.jqDeleteButtonContainer.hide();
			}	
		}
		
		private send(id: string) {
			this.form.submit();
		}
		
		private onSubmit() {
			this.overviewContent.clear(true);
		}
		
		private whenSubmitted(data) {
			var jqForm = $(n2n.ajah.analyze(data));
			this.form.jQuery.replaceWith(jqForm);
			this.form = null;
			n2n.ajah.update();
			this.init(jqForm);
			this.overviewContent.init(1);
		}
	}
}