jQuery(document).ready(function($) {
	(function() {
		var jqFilterForm = $('#rocket-filter');
		if (jqFilterForm.length === 0) return;
		/**
		 * class Filter
		 */
		function Filter(jqFilterForm, jqConfigurationPanel, jqFilterSelect, jqSelectFilterButton) {
			this.scriptId = jqFilterForm.data("rocket-script-id");
			this.open = rocket.getCookie(this.scriptId) === "true" || false;
			this.jqConfigurationPanel = jqConfigurationPanel;
			jqSelectFilterButton.css("display", "none");
		
			jqFilterSelect.change(function() {
				jqSelectFilterButton.click();
			});
			
			var jqConfigIcon = $("<i/>", {"class": "fa fa-expand"});
			var jqToogleConfigButton = $("<button/>", {"type": "button", "class": "rocket-filter-configuration-toggle rocket-control rocket-command-lonely-appended"});
			jqToogleConfigButton.append(jqConfigIcon);
			jqFilterSelect.after($("<span/>").append(jqToogleConfigButton));
			
			var jqElemDeactivateFilter = jqFilterForm.find(".rocket-control[name=meth-clear]").hide();
			if (jqElemDeactivateFilter.length > 0) {
				var jqEraseFilterIcon = $("<i/>", {"class": "fa fa-eraser"});
				var jqEraseFilterButton = $("<button/>", {"type": "button", "class": "rocket-filter-configuration-toggle rocket-control rocket-command-lonely-appended",
						"title": jqElemDeactivateFilter.val() }).click(function() {
					jqElemDeactivateFilter.click();
				});
				jqEraseFilterButton.append(jqEraseFilterIcon);
				jqToogleConfigButton.after($("<span/>").append(jqEraseFilterButton));
			}
			
			var _obj = this;
			var configPanel = function(slideDuration) {
				if (_obj.open) {
					jqConfigIcon.attr("class", "fa fa-compress");
					if (slideDuration) {
						jqConfigurationPanel.slideDown(slideDuration);
					} else {
						jqConfigurationPanel.show();
					}
				} else {
					jqConfigIcon.attr("class", "fa fa-expand");
					if (slideDuration) {
						jqConfigurationPanel.slideUp(slideDuration);
					} else {
						jqConfigurationPanel.hide();
					}
				}
			};
			
			jqToogleConfigButton.click(function() {
				_obj.setOpen(!_obj.open);
				configPanel(100);
			});
			configPanel();
		}
		
		Filter.prototype.setOpen = function(open) {
			if (this.open == open) return;
			this.open = open;
			rocket.setCookie(this.scriptId, open);
		}

		new Filter(jqFilterForm, jqFilterForm.find(".rocket-filter-configuration"),
				jqFilterForm.find("select[name=prop-selectedFilterId]"), 
				jqFilterForm.find("input[name=meth-selectFilter]"));
	})();
});