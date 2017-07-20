/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
module ui {
	$ = jQuery;
	export class MultiAdd {
		private elemOpener: JQuery;
		private elemContent: JQuery;
		private elemContentContainer: JQuery;
		private mouseLeaveTimeOut: string;
		private elemWindow: JQuery;
		private alignment: string;
		private elemArrow: JQuery;
		public static ALIGNMENT_LEFT: string = 'left';
		public static ALIGNMENT_RIGHT: string = 'right';
		
		public constructor(elemOpener: JQuery, elemContent: JQuery, 
				alignment: string = MultiAdd.ALIGNMENT_RIGHT) {
			this.elemOpener = elemOpener;
			this.elemContent = elemContent;
			this.elemContentContainer = jQuery("<div />", {
				"class": "rocket-multi-add-content-container"
			}).css({
				"position": "fixed",
				"zIndex": 1000
			}).hide();
			this.elemWindow = jQuery(window);
			this.alignment = alignment;
			
			(function(that: MultiAdd) {
				that.elemContentContainer.insertBefore(elemContent)
						.append(elemContent);
				
				that.elemArrow = $("<span />").insertAfter(that.elemContentContainer).css({
					"position": "fixed",
					"background": "#818a91",
					"transform": "rotate(45deg)",
					"width": "15px",
					"height": "15px" 
				}).addClass("rocke-multi-add-arrow-left").hide();
				
				elemOpener.click(function(e) {
					e.preventDefault();
					e.stopPropagation();
					that.showContent();
				});
				
				that.elemContentContainer.click(function() {
					that.elemArrow.hide();
				});
			}).call(this, this);
		}
					
	 	public showContent = function() {
			this.elemContentContainer.show();
			var left = this.determineContentLeftPos();
			this.elemArrow.show().css({
				"top": this.elemOpener.offset().top + (this.elemOpener.outerHeight() / 2) 
						- (this.elemArrow.outerHeight() / 2),
				"left": left + 2
			});
			  
			if (this.alignment === MultiAdd.ALIGNMENT_RIGHT) {
				left += this.elemArrow.outerWidth() / 2
			} else {
				left -= this.elemArrow.outerWidth()	/ 2
			}
			  
			this.elemContentContainer.css({
				"top": this.determineContentTopPos(),
				"left": left
			});
			this.applyMouseLeave();
		}
		
		private determineContentLeftPos() {
			if (this.alignment === MultiAdd.ALIGNMENT_RIGHT) {
				return this.elemOpener.offset().left + this.elemOpener.outerWidth();
				
			} 
			
			return this.elemOpener.offset().left - this.elemContentContainer.outerWidth();
		}
		
		private determineContentTopPos() {
			 return this.elemOpener.offset().top +  this.elemOpener.outerHeight() / 2 -
					this.elemWindow.scrollTop() - (this.elemContentContainer.outerHeight() / 2);
		}
				
		private applyMouseLeave = function() {
			var that = this;
			this.resetMouseLeave();
			
			this.elemContentContainer.on("mouseenter.multi-add", function() {
				that.applyMouseLeave();
			}).on("mouseleave.multi-add", function() {
				that.mouseLeaveTimeout = setTimeout(function() {
					that.hideContent();
				}, 1000);
			}).on("click.multi-add", function(e) {
				e.stopPropagation();
			});
			
			this.elemWindow.on("keyup.multi-add", function(e) {
				if (e.which === 27) {
					//escape	
					that.hideContent();	
				};
			}).on("click.multi-add", function() {
				that.hideContent();
			});
		};
	
		private hideContent = function() {
			this.elemContentContainer.hide();
			this.elemArrow.hide();
			this.resetMouseLeave();
		};
	
	 	private resetMouseLeave = function() {
			if (null !== this.mouseLeaveTimeout) {
				clearTimeout(this.mouseLeaveTimeout);
				this.mouseLeaveTimeout = null;
			}
			
			this.elemContentContainer.off("mouseenter.multi-add mouseleave.multi-add click.multi-add");
			this.elemWindow.off("keyup.multi-add click.multi-add");
		};
	}
	
	export interface StackedContent {
		getTitle(): string;
		getElemContent(): JQuery;
		setup(stackedContentContainer: StackedContentContainer);
		onClose();
		onClosed();
		onAnimationComplete();
	}
	
	export class StackedContentContainer {
		private contentStack: ContentStack;
		private elem: JQuery;
		private elemClose: JQuery;
		private elemFooter: JQuery;
		private elemControls: JQuery;
		private stackedContent: StackedContent;
		
		public constructor(contentStack: ContentStack, stackedContent: StackedContent) {
			this.contentStack = contentStack;
			this.stackedContent = stackedContent;
			this.elem = $("<div />", {
				"class": "rocket-content-container"	
			});
			
			this.elemFooter = $("<div />", {
				"class": "rocket-page-controls"	
			}).css({
				"height": $("#rocket-page-controls").innerHeight()
			});
			
			this.elemControls = $("<ul />").appendTo(this.elemFooter);
			
			(function(that: StackedContentContainer) {
				that.elemClose = $("<a />", {
					"href": "#",
					"class": "rocket-stacked-content-close"
				}).append($("<i />", {
					"class": "fa fa-times"	
				})).appendTo(that.elem).click(function(e) {
					e.preventDefault();
					that.close();
				});
				that.elem.append(stackedContent.getElemContent());
				stackedContent.setup(that);
			}).call(this, this);
		}
		
		public addControl(iconClassName, label, callback) {
			$("<button />", {
				"class": "rocket-control",
				"text": label
			}).click(function(e) {
				e.preventDefault();
				callback();
			}).appendTo($("<li />").appendTo(this.elemControls));
		}
		
		public close() {
			var that = this;
			that.stackedContent.onClose();
			this.contentStack.popStackedContent(this, function() {
				that.stackedContent.onClosed();
				that.elem.detach();
				that.elemFooter.detach();
			});
		}
		
		public getElem() {
			return this.elem;	
		}
		
		public getElemFooter() {
			return this.elemFooter;	
		}
	}
	
	export class ContentStack {
		private elemContentContainer: JQuery;
		private stackedContentContainers: Array<StackedContentContainer> = [];
		private animationSpeed = 175;
		private targetLeft =  215;
		
		public constructor(contentContainer: JQuery) {
			this.elemContentContainer = contentContainer;
			
			(function(that: ContentStack) {
				$(window).on("keyup.contentStack", function(e) {
					if (e.which != 27) return;
					
					that.closeCurrent();
				});
			}).call(this, this);
		}
		
		public closeCurrent() {
			if (this.stackedContentContainers.length === 0) return;
			
			this.stackedContentContainers.pop().close();
		}
		
		public addStackedContent(stackedContent: StackedContent) {
			var stackedContentContainer = new StackedContentContainer(this, stackedContent),
				zIndex = this.stackedContentContainers.length + 100,
				outerWidth = this.elemContentContainer.outerWidth();
			stackedContentContainer.getElem().appendTo(this.elemContentContainer).css({
				"zIndex": zIndex,
				"left": this.targetLeft + outerWidth,
				"right": -outerWidth
			}).animate({
				"left": this.targetLeft,
				"right": 0
			}, this.animationSpeed, function() {
				stackedContent.onAnimationComplete();
			});
			
			stackedContentContainer.getElemFooter().appendTo(this.elemContentContainer).css({
				"zIndex": zIndex,
				"left": this.targetLeft + outerWidth,
				"right": -outerWidth
			}).animate({
				"left": this.targetLeft,
				"right": 0
			}, this.animationSpeed);
			
			this.stackedContentContainers.push(stackedContentContainer);
		}

		public popStackedContent(stackedContentContainer, callback: () => void) {
			var that = this, 
				outerWidth = this.elemContentContainer.outerWidth();
			stackedContentContainer.getElem().animate({
				"left": outerWidth + this.targetLeft,
				"right": -outerWidth
			}, this.animationSpeed, function() {
				that.stackedContentContainers.splice(
						that.stackedContentContainers.indexOf(stackedContentContainer), 1);
				callback();
			});
			
			stackedContentContainer.getElemFooter().animate({
				"left": outerWidth + this.targetLeft,
				"right": -outerWidth
			}, this.animationSpeed);
		}
	}
	
	export class AdditionalContentEntry {
		private additionalContent: AdditionalContent;
		private elemActivator: JQuery;
		private elemContent: JQuery;
		
		public constructor(additionalContent: AdditionalContent, title: string, elemContent: JQuery) {
			this.additionalContent = additionalContent;
			this.elemContent = elemContent;
			
			(function(that: AdditionalContentEntry) {
				that.elemActivator = $("<a />", {
					"text": title,
					"href": "#"	
				}).click(function(e) {
					e.preventDefault();
					additionalContent.activate(that);
				});
			}).call(this, this);
		}
		
		public activate() {
			this.elemActivator.addClass("active");
			this.elemContent.show();
		}
		
		public deactivate() {
			this.elemActivator.removeClass("active");
			this.elemContent.hide();
		}
		
		public getElemActivator() {
			return this.elemActivator;	
		}
		
		public getElemContent() {
			return this.elemContent;	
		}
		
		public static create(additionalContent: AdditionalContent, elemDiv: JQuery) {
			var elemH2 = elemDiv.children("h2:first"),
				title = elemH2.text(),
				elemContent = elemH2.remove();
			return new AdditionalContentEntry(additionalContent, title, elemContent);
		}
	}
	
	export class AdditionalContent {
		private elem: JQuery; 
		private elemUlTabs: JQuery;
		private activeEntry: AdditionalContentEntry = null;
		private elemContentContainer: JQuery;
		private elemContent: JQuery;
		
		public constructor(elemContentContainer: JQuery, elem: JQuery = null) {
			this.elemContent = elemContentContainer.children(".rocket-content");
			if (null === elem) {
				this.elem = $("<div />", {
					"id": "rocket-additional"	
				}).insertAfter(this.elemContent);
				
				this.elemContent.addClass("rocket-contains-additional");
			} else {
				this.elem = elem;	
			}
			
			
			this.elem.addClass("rocket-grouped-panels");
			rocketTs.markAsInitialized(".rocket-grouped-panels", this.elem);
			
			this.elemUlTabs = $("<ul />", {
				"class": "rocket-grouped-panels-navigation"	
			}).appendTo(this.elem);
			this.elemContentContainer = $("<div />", {
				"class": "rocket-additional-container"	
			}).appendTo(this.elem);
			
			(function(that: AdditionalContent) {
				if (null !== elem) {
					elem.children().each(function() {
						that.appendEntry(AdditionalContentEntry.create(that, $(this)));
					});
				}
			}).call(this, this);
		}
		
		public getElemContent() {
			return this.elemContent;	
		}
		
		public activate(entry: AdditionalContentEntry) {
			if (null !== this.activeEntry) {
				this.activeEntry.deactivate();	
			}
			this.activeEntry = entry;
			entry.activate();
		}
		
		private appendEntry(entry: AdditionalContentEntry) {
			this.elemUlTabs.append($("<li />").append(entry.getElemActivator()));
			this.elemContentContainer.append(entry.getElemContent());
			
			if (null === this.activeEntry) {
				this.activate(entry);	
			}
		}
		
		public createAndPrependEntry(title: string, elemContent: JQuery, activate: boolean = true) {
			var entry = new AdditionalContentEntry(this, title, elemContent);
			this.appendEntry(entry);
			
			if (activate) {
				entry.activate();
			}
		}
	}
	
	export class UnsavedFormManager {
		private listining: boolean = false;
		private elemWindow: JQuery;
		
		public constructor() {
			this.elemWindow = $(window);
			
			(function(that: UnsavedFormManager) {
				that.elemWindow.load(function() {
					that.listining = true;
				});
			}).call(this, this);
		}
		
		public registerForm(elemForm: JQuery) {
			if (!elemForm.is("form")) throw "Not a Form";
			var that = this;
			elemForm.on('submit.unsavedFormManager', function() {
				that.deactivate();
			}).on("keydown.unsavedFormManager change.unsavedFormManager", function() {
				if (that.activate(elemForm.data("text-unload"))) {
					elemForm.off("keydown.unsavedFormManager change.unsavedFormManager");
				}
			});
		}
			
		public activate = function(text) {
			if ((!this.listening)) return false;
			
			this.elemWindow.off('beforeunload.unsavedFormManager').on('beforeunload.unsavedFormManager', function(){
				return text || "Your changes won't be saved.";
			});
			
			return true;
		};
		
		public deactivate = function() {
			this.elemWindow.off('beforeunload.unsavedFormManager');
		};
	}
	
	export class EntryFormCommand {
		private elemContainer: JQuery;
		private elemButton: JQuery;
		
		public constructor(text: string, callback: () => void = null, iconClassName: string = null) {
			this.elemContainer = $("<div />", {
				"class": "rocket-entry-form-command"
			});
			
			this.elemButton = $("<button />", {
				"text": text,
				"class": "rocket-control rocket-control-full"
			}).click(function(e) {
				e.preventDefault();
				callback();
			}).appendTo(this.elemContainer);
			
			if (null !== iconClassName) {
				this.elemButton.prepend($("<i />", {
					"class": iconClassName	
				}));
			}
		}
		
		public getElemContainer(): JQuery {
			return this.elemContainer;	
		}
		
		public getElemButton(): JQuery {
			return this.elemButton;	
		}
		
		public setLoading(loading: boolean) {
			if (loading) {
				this.elemButton.addClass("rocket-loading");
			} else {
				this.elemButton.removeClass("rocket-loading");
			}
		}
	}
}
