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
			
			this.critmodForm = new CritmodForm(this.overviewContent);
			this.critmodForm.init(this.jqElem.find("form.rocket-impl-critmod:first"));
			
			this.critmodSelect = new CritmodSelect(this.overviewContent);
			this.critmodSelect.init(this.jqElem.find("form.rocket-impl-critmod-select:first"), this.critmodForm);
			
			this.critmodForm.drawControl(this.critmodSelect.jQuery.parent());
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
		private critmodForm: CritmodForm;
		
		private jqSelect: JQuery;
		private jqButton: JQuery;
		
		constructor(private overviewContent: OverviewContent) {
		}
		
		get jQuery(): JQuery {
			return this.form.jQuery;
		}
		
		public init(jqForm: JQuery, critmodForm: CritmodForm) {
			if (this.form) {
				throw new Error("CritmodSelect already initialized.");
			}
			
			this.form = Form.from(jqForm);
			this.form.reset();
			
			this.critmodForm = critmodForm;
			
			this.jqButton = jqForm.find("button[type=submit]").hide();
			
			this.form.config.blockContext = false;
			this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
			this.form.config.autoSubmitAllowed = false;
			
			var that = this;
			this.form.config.successResponseHandler = function (data: string) {
				that.whenSubmitted(data);
			}
			this.jqSelect = jqForm.find("select:first").change(function () {
				that.send();
			});
			
			critmodForm.onChange(function () {
				that.form.abortSubmit();
				that.updateId();
			});
			
			critmodForm.whenChanged(function (idOptions) {
				that.updateIdOptions(idOptions);
			});
		}
		
//		private sc = 0;
//		private serachVal = null;
//		
		private updateState() {
			if (this.jqSelect.val()) {
				this.form.jQuery.addClass("rocket-active");
			} else {
				this.form.jQuery.removeClass("rocket-active");
			}
		}
		
		private send() {
			this.form.submit({ button: this.jqButton.get(0) });
		
			this.updateState();
			this.overviewContent.clear(true);
			
			var id = this.jqSelect.val();
			this.critmodForm.activated = id == true;
			this.critmodForm.critmodSaveId = id;
			this.critmodForm.freeze(); 
		}
		
		private whenSubmitted(data) {
			this.overviewContent.initFromResponse(data);
			this.critmodForm.reload();
		}
		
		private updateId() {
			var id = this.critmodForm.critmodSaveId;
			if (id && isNaN(parseInt(id))) {
				this.jqSelect.append($("<option />", { "value": id, "text": this.critmodForm.critmodSaveName }));
			}
			
			this.jqSelect.val(id);
			this.updateState();
			
		}
		
		private updateIdOptions(idOptions) {
			this.jqSelect.empty();
			
			for (let id in idOptions) {
				this.jqSelect.append($("<option />", { value: id, text: idOptions[id] }));	
			}	
			
			this.jqSelect.val(this.critmodForm.critmodSaveId);
		}
	}
	
	class CritmodForm {
		private form: Form;
		
		private jqApplyButton: JQuery;
		private jqClearButton: JQuery;
		private jqNameInput: JQuery;
		private jqSaveButton: JQuery;
		private jqSaveAsButton: JQuery;
		private jqDeleteButton: JQuery;
		
		private jqOpenButton: JQuery;
		private jqEditButton: JQuery;
		private jqCloseButton: JQuery;
		
		private changeCallbacks: Array<() => any> = [];
		private changedCallbacks: Array<(idOptions: {[key: string]: string}) => any> = [];
		
		private _open: boolean = true;
		
		constructor(private overviewContent: OverviewContent) {
		}
		
		public drawControl(jqControlContainer: JQuery) {
			var that = this;
			
			this.jqOpenButton = $("<button />", { 
						"class": "btn btn-secondary", 
						"text": jqControlContainer.data("rocket-impl-open-filter-label") + " "
					})
					.append($("<i />", { "class": "fa fa-filter"}))
					.click(function () { that.open = true })
					.appendTo(jqControlContainer);
			
			this.jqEditButton = $("<button />", { 
						"class": "btn btn-secondary", 
						"text": jqControlContainer.data("rocket-impl-edit-filter-label") + " "
					})
					.append($("<i />", { "class": "fa fa-filter"}))
					.click(function () { that.open = true })
					.appendTo(jqControlContainer);
			
			this.jqCloseButton = $("<button />", { 
						"class": "btn btn-secondary", 
						"text": jqControlContainer.data("rocket-impl-close-filter-label") + " "
					})
					.append($("<i />", { "class": "fa fa-times"}))
					.click(function () { that.open = false })
					.appendTo(jqControlContainer);
			
			this.open = false;
		}
		
		private updateControl() {
			if (!this.jqOpenButton) return;
			
			if (this.open) {
				this.jqOpenButton.hide();
				this.jqEditButton.hide();
				this.jqCloseButton.show();
				return;
			} 
			
			if (this.critmodSaveId) {
				this.jqOpenButton.hide();
				this.jqEditButton.show();
			} else {
				this.jqOpenButton.show();
				this.jqEditButton.hide();
			}
		
			this.jqCloseButton.hide();
		}
		
		get open() {
			return this._open;
		}
		
		set open(open: boolean) {
			this._open = open;
			
			if (open) {
				this.form.jQuery.show();
			} else {
				this.form.jQuery.hide();
			}
			
			this.updateControl();
		}
		
		public init(jqForm: JQuery) {
			if (this.form) {
				throw new Error("CritmodForm already initialized.");
			}
			
			this.form = Form.from(jqForm);
			this.form.reset();
			
			this.form.config.blockContext = false;
			this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");

			var that = this;
			this.form.config.successResponseHandler = function (data: string) {
				that.whenSubmitted(data);
			};
			
			var activateFunc = function (ensureCritmodSaveId: boolean) { 
				that.activated = true;
				
				if (ensureCritmodSaveId && !that.critmodSaveId) {
					that.critmodSaveId = "new";
				}
				that.onSubmit();
			}
			var deactivateFunc = function () { 
				that.activated = false; 
			 	that.critmodSaveId = null;
				
				that.onSubmit();
			}
			
			this.jqApplyButton = jqForm.find(".rocket-impl-critmod-apply").click(function () { activateFunc(false); });
			this.jqClearButton = jqForm.find(".rocket-impl-critmod-clear").click(function () { deactivateFunc(); });
			this.jqNameInput = jqForm.find(".rocket-impl-critmod-name");
			this.jqSaveButton = jqForm.find(".rocket-impl-critmod-save").click(function () { activateFunc(true); });
			this.jqSaveAsButton = jqForm.find(".rocket-impl-critmod-save-as").click(function () {
				that.critmodSaveId = null;
				activateFunc(true); 
			});
			this.jqDeleteButton = jqForm.find(".rocket-impl-critmod-delete").click(function () { deactivateFunc(); });
			
			this.updateState();
		}
		
		get activated(): boolean {
			return this.form.jQuery.hasClass("rocket-active");
		}
		
		set activated(activated: boolean) {
			if (activated) {
				this.form.jQuery.addClass("rocket-active");
			} else {
				this.form.jQuery.removeClass("rocket-active");
			}
		}
		
		get critmodSaveId(): string {
			return this.form.jQuery.data("rocket-impl-critmod-save-id");
		}
		
		set critmodSaveId(critmodSaveId: string) {
			this.form.jQuery.data("rocket-impl-critmod-save-id", critmodSaveId);
			
			this.updateControl();
		}
		
		get critmodSaveName(): string {
			return this.jqNameInput.val();
		}
						
		private updateState() {
			if (this.critmodSaveId) {
				this.jqSaveAsButton.show();
				this.jqDeleteButton.show();
			} else {
				this.jqSaveAsButton.hide();
				this.jqDeleteButton.hide();
			}
		}
		
		public freeze() {
			this.form.abortSubmit();
		}
		
		public reload() {
			var url = this.form.config.actionUrl;
			
			var that = this;
			$.ajax({
				"url": url,
				"dataType": "json"
			}).fail(function (jqXHR, textStatus, data) {
				if (jqXHR.status != 200) {
                    rocket.getContainer().handleError(url, jqXHR.responseText);
					return;
				}
				
				throw new Error("invalid response");
			}).done(function (data, textStatus, jqXHR) {
				that.replaceForm(data);
			});
		}
		
		private onSubmit() {
			this.changeCallbacks.forEach(function (callback) {
				callback();
			});
			
			this.overviewContent.clear(true);
		}
		
		private whenSubmitted(data) {
			this.overviewContent.init(1);
			
			this.replaceForm(data);
		}
		
		private replaceForm(data) {
			var jqForm = $(n2n.ajah.analyze(data));
			this.form.jQuery.replaceWith(jqForm);
			this.form = null;
			n2n.ajah.update();
			this.init(jqForm);
			
			this.open = this.open;
			this.updateControl();
			
			var idOptions = data.additional.critmodSaveIdOptions;
			this.changedCallbacks.forEach(function (callback) {
				callback(idOptions);
			});
		}
		
		public onChange(callback: () => any) {
			this.changeCallbacks.push(callback);
		}
		
		public whenChanged(callback: (idOptions: {[key: string]: string}) => any) {
			this.changedCallbacks.push(callback);
		}
	}
}