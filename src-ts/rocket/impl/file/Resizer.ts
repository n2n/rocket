namespace Rocket.Impl.File {
	class SelectableDimension implements SizeSelectorListener {
		private elemLowRes: JQuery;
		private elemRadio: JQuery;
		private elemThumb: JQuery;
		private dimensionStr: string;
		private ratioStr: string;
		private ratio: boolean;
		private resizingDimension: ResizingDimension;
	
		public constructor(private resizer: Resizer, private elemLi: JQuery) {
			this.resizer.getSizeSelector().registerChangeListener(this);
			this.elemLowRes = elemLi.find(".rocket-image-low-res").hide();
			this.elemRadio = elemLi.find("input[type=radio]:first");
			this.elemThumb = elemLi.find(".rocket-image-previewable:first");
			this.dimensionStr = this.elemRadio.data('dimension-str').toString();
			this.ratioStr = elemLi.data('ratio-str').toString();
			this.ratio = this.ratioStr === this.elemRadio.val();
			
			(function(that: SelectableDimension) {
				if (that.elemRadio.is(":checked")) {
					that.resizer.setSelectedDimension(that, false);
				}
				
				that.elemRadio.change(function() {
					if (that.elemRadio.is(":checked")) {
						that.resizer.setSelectedDimension(that, true);
					}
				});
				
				if (!this.ratio) {
					if (that.elemThumb.length > 0) {
					    this.elemLi.append($("<a />", {
					        "href": "#"
					    }).click(function(e) {
					        e.preventDefault();
					        that.elemThumb.click();
					        that.elemLi.nextUntil(".rocket-image-ratio")
					    }).append($("<i />", {
					        "class": "fa fa-search"
					    })));
					}
				} else {
					var elemToggleOpen = $("<i />", {
						"class": "fa fa-angle-down"
					});
					var elemToggleClose = $("<i />", {
						"class": "fa fa-angle-up"
					});
					var elemsToToggle = that.elemLi.siblings("[data-ratio-str=" + that.ratioStr + "]");
					this.elemLi.append($("<a />", {
				        "href": "#",
				        "class": "open"
				    }).click(function(e) {
				        e.preventDefault();
				        var elem = $(this);
				        if (elem.hasClass("open")) {
				        	elemsToToggle.hide();
				        	elem.removeClass("open");
				        	elemToggleOpen.show();
				        	elemToggleClose.hide();
				        } else {
				        	elemsToToggle.show();
				        	elem.addClass("open");
				        	elemToggleOpen.hide();
				        	elemToggleClose.show();
				        }
				    }).append(elemToggleOpen).append(elemToggleClose).click());
				}
			}).call(this, this);
		}
		
		public getDimensionStr() {
			return this.dimensionStr;
		}
		
		public getRatioStr() {
			return this.ratioStr;
		}
		
		public isRatio() {
			return this.ratio;
		}
		
		public select() {
			this.elemRadio.prop("checked", true);
		}
		
		public buildStorageKey() {
			return location.href + '/' + this.ratioStr;
		}
		
		public hasSameRatio(selectableDimension: SelectableDimension) {
			return selectableDimension.getRatioStr() === this.ratioStr;
		}
		
		public equals(selectableDimension: SelectableDimension) {
			return this.hasSameRatio(selectableDimension) && selectableDimension.getDimensionStr() === this.dimensionStr 
					&& this.isRatio() === selectableDimension.isRatio();
		}
		
		public onDimensionChange(sizeSelector: SizeSelector) {
			let currentResizingDimension = sizeSelector.getCurrentResizingDimension(),
				currentSelectableDimension = currentResizingDimension.getSelectableDimension();
			
			if (((currentSelectableDimension.isRatio() && currentSelectableDimension.hasSameRatio(this)) 
					|| currentSelectableDimension.equals(this)) && sizeSelector.isLowRes(this.createResizingDimension())) {
				this.elemLowRes.show();
			} else {
				this.elemLowRes.hide();
			}
			
		}
		
		public createResizingDimension() {
			return new ResizingDimension(this, this.resizer.getZoomFactor());
		}
	}
	
	class ResizingDimension {
		static dimensionMatchPattern = new RegExp("\\d+x\\d+[xcrop]?"); 
		private width: number; 
		private height: number; 
		private crop: boolean; 
		private ratio: number = 1;
	
		public constructor(private selectableDimension: SelectableDimension, private zoomFactor: number) {
			this.initialize();
		}

		public initialize() {
			var dimensionStr = this.selectableDimension.getDimensionStr();
			if (dimensionStr.match(ResizingDimension.dimensionMatchPattern) === null) return;
			
			let dimension: Array<string> = dimensionStr.split("x");
			this.width = parseInt(dimension[0]) * this.zoomFactor;
			this.height = parseInt(dimension[1]) * this.zoomFactor;
			if (dimension.length <= 2) {
				this.crop = false;
			} else {
				this.crop = dimension[2].startsWith("c");
			}
			this.ratio = this.width / this.height;
		}
		
		public getSelectableDimension() {
			return this.selectableDimension;
		}

		public isCrop(): boolean {
			return this.crop;
		}

		public getRatio(): number {
			return this.ratio;
		}

		public getWidth(): number {
			return this.width;
		}

		public getHeight(): number {
			return this.height;
		}
		
		public buildStorageKey() {
			return this.getSelectableDimension().buildStorageKey();
		}
	}

	class ResizerToolbar {
		private elemUl: JQuery = null;
		private elemUlImageVersions: JQuery = null;
		private elemCbxFixedRatio: JQuery = null;
		private elemLiFixedRatio: JQuery = null;
		private elemSpanWarning: JQuery = null;
		private elemSpanZoom: JQuery = null;

		public constructor(private imageResizer: Resizer, private elemDimensionContainer: JQuery) {
			this.elemSpanZoom = $("<span />");
		}
		
		public getElemSpanZoom() {
			return this.elemSpanZoom;
		}

		public initializeUi() {
			var _obj = this;

			this.elemUl = $("<ul/>").css({
				margin: "0px"
			});
			
			var randomId = "rocket-image-resizer-fixed-ratio-" + Math.floor((Math.random() * 10000));

			this.elemLiFixedRatio = $("<li/>").addClass("rocket-fixed-ratio").append(
				$("<label/>").attr("for", randomId).css("display", "inline-block").text(_obj.imageResizer.textFixedRatio));
			this.elemCbxFixedRatio = $("<input type='checkbox'/>").addClass("rocket-image-resizer-fixed-ratio").attr("id", randomId)
				.change(function() {
					let sizeSelector = _obj.imageResizer.getSizeSelector();
					sizeSelector.setFixedRatio($(this).prop("checked"));
					sizeSelector.initializeMin();
					sizeSelector.initializeMax();
				});

			this.elemLiFixedRatio.append(this.elemCbxFixedRatio);
			this.elemUl.append(this.elemLiFixedRatio);
			this.elemSpanWarning = $("<span/>").addClass("rocket-image-resizer-warning").text(this.imageResizer.textLowResolution).hide();
			this.elemUl.append($("<li/>").addClass("rocket-low-resolution").append(this.elemSpanWarning));
			this.elemUl.append($("<li/>").append(this.elemSpanZoom));

			this.imageResizer.getElemToolbar().append(this.elemUl);
		}

		public redraw(resizingDimension: ResizingDimension) {
			if (resizingDimension.isCrop()) {
				this.elemCbxFixedRatio.prop("checked", true);
				this.elemLiFixedRatio.hide();
			} else {
				this.elemCbxFixedRatio.prop("checked", true);
				this.elemLiFixedRatio.show();
			}
			this.elemCbxFixedRatio.trigger("change");
		}
		
		public showWarning() {
			this.elemSpanWarning.show();
		}
		
		public hideWarning() {
			this.elemSpanWarning.hide();
		}
	}

	class Dimension {
		public top: number;
		public right: number;
		public bottom: number;
		public left: number;

		public constructor(public width: number, public height: number) {

		}
	}

	interface SizeSelectorListener {
		onDimensionChange(sizeSelector: SizeSelector): void;
	}

	class DragStart {
		public positionTop: number = null;
		public positionLeft: number = null;
		public mouseOffsetTop: number = null;
		public mouseOffsetLeft: number = null;
	}

	class ResizeStart {
		public width: number = null;
		public height: number = null;
		public mouseOffsetTop: number = null;
		public mouseOffsetLeft: number = null;
	}

	class SizeSelector {
		private fixedRatio: boolean = false;
		private currentResizingDimension: ResizingDimension = null;
		private elemDiv: JQuery = null;
		private elemSpan: JQuery = null;
		private imageLoaded: boolean = false;
		private dragStart: DragStart = null;
		private resizeStart: ResizeStart = null;
		private max: Dimension = null;
		private min: Dimension = null;
		private changeListeners: Array<SizeSelectorListener> = [];

		public constructor(private imageResizer: Resizer, private elemImg: JQuery) {
			this.initialize();
		}
		
		public getCurrentResizingDimension() {
			return this.currentResizingDimension;
		}

		public getPositionTop() {
			return this.elemDiv.position().top;
		}

		public getPositionLeft() {
			return this.elemDiv.position().left;
		}

		public getWidth() {
			return this.elemDiv.width();
		}

		public getHeight() {
			return this.elemDiv.height();
		}

		public setFixedRatio(fixedRatio: boolean) {
			this.fixedRatio = fixedRatio;
			this.checkRatio();
		}
		
		private initialize() {
			this.initializeResizeStart();
			this.initializeDragStart();
		}

		private checkRatio() {
			if (!this.fixedRatio || !this.currentResizingDimension) return;

			var width = this.elemDiv.width();
			var height = this.elemDiv.height();
			if (width < height) {
				this.elemDiv.height(width / this.currentResizingDimension.getRatio());
			} else {
				this.elemDiv.width(height * this.currentResizingDimension.getRatio());
			}

			this.elemDiv.trigger('sizeChange');
		}

		public initializeMin() {
			let spanHeight: number = this.elemSpan.height();
			if (this.fixedRatio && null !== this.currentResizingDimension) {
				var ratio = this.currentResizingDimension.getRatio();
				if (this.currentResizingDimension.getWidth() > this.currentResizingDimension.getHeight()) {
					this.min = new Dimension(spanHeight * ratio, spanHeight);
				} else {
					this.min = new Dimension(spanHeight, spanHeight / ratio);
				}
			} else {
				this.min = new Dimension(this.elemSpan.width(), this.elemSpan.height());
			}
		}

		public initializeMax() {
			let imageWidth = this.elemImg.width(),
				imageHeight = this.elemImg.height(),
				dimensionWidth: number,
				dimensionHeight: number;

			if (this.fixedRatio && null !== this.currentResizingDimension) {
				var ratio = this.currentResizingDimension.getRatio();
				dimensionWidth = imageHeight * ratio;
				if (dimensionWidth > imageWidth) {
					dimensionWidth = imageWidth;
				}
				dimensionHeight = imageWidth / ratio;
				if (dimensionHeight > imageHeight) {
					dimensionHeight = imageHeight;
				}
			} else {
				dimensionWidth = imageWidth;
				dimensionHeight = imageHeight;
			}

			this.max = new Dimension(dimensionWidth, dimensionHeight);
			this.max.top = 0;
			this.max.left = 0;
			this.max.right = imageWidth;
			this.max.bottom = imageHeight;
		}

		private initializeDragStart() {
			this.dragStart = new DragStart();
		}

		private initializeResizeStart() {
			this.resizeStart = new ResizeStart();
		}

		private checkPositionRight(newRight: number): boolean {
			return this.max.right > newRight;
		}

		private checkPositionLeft(newLeft: number): boolean {
			return this.max.left < newLeft;
		}

		private checkPositionBottom(newBottom: number): boolean {
			return this.max.bottom > newBottom;
		}

		private checkPositionTop(newTop: number) {
			return this.max.top < newTop;
		}

		private checkPositions(newTop: number, newRight: number, newBottom: number, newLeft: number): boolean {
			return this.checkPositionTop(newTop) && this.checkPositionRight(newRight)
				&& this.checkPositionBottom(newBottom) && this.checkPositionLeft(newLeft);
		}
		
		public isLowRes(resizingDimension: ResizingDimension = null) {
			if (!resizingDimension) {
				resizingDimension = this.currentResizingDimension;
			}
			
			if (!resizingDimension) return false;
			
			return resizingDimension.getWidth() > (this.getWidth() + 1)
					|| resizingDimension.getHeight() > (this.getHeight() + 1);
		}

		public initializeUI() {
			var _obj = this;
			if (!this.imageLoaded) {
				this.elemDiv = $("<div/>").css({
					zIndex: 40,
					position: "absolute",
					overflow: "hidden"
				}).addClass("rocket-image-resizer-size-selector");

				//Image
				this.elemImg = $("<img/>").css("position", "relative");

				this.elemImg.on("load", () => {
					this.imageLoaded = true;
					this.initializeUI();
					
					window.scroll(0, Jhtml.Monitor.of(this.elemImg.get(0)).history.currentPage.config.scrollPos);
				}).attr("src", this.imageResizer.getElemImg().attr("src"));

				this.elemDiv.append(this.elemImg);

				this.imageResizer.getElemContent().append(this.elemDiv);

				this.elemSpan = $("<span/>").css({
					zIndex: 41,
					position: "absolute",
					right: "-1px",
					bottom: "-1px"
				});
			} else {
				this.imageResizer.getElemContent().css({
					position: "relative"
				});
				this.elemImg.width(this.imageResizer.getElemImg().width()).height(this.imageResizer.getElemImg().height());

				this.elemDiv.mousedown(function(event) {
					//remember oldPositions
					_obj.dragStart.positionTop = _obj.elemDiv.position().top;
					_obj.dragStart.positionLeft = _obj.elemDiv.position().left;
					_obj.dragStart.mouseOffsetTop = event.pageY;
					_obj.dragStart.mouseOffsetLeft = event.pageX;

					$(document).on('mousemove.drag', function(event) {
						//var borderWidth = (_obj.elemDiv.outerWidth() - _obj.elemDiv.innerWidth()) / 2;
						var newTop = _obj.dragStart.positionTop - (_obj.dragStart.mouseOffsetTop - event.pageY);
						var newLeft = _obj.dragStart.positionLeft - (_obj.dragStart.mouseOffsetLeft - event.pageX);
						var newRight = newLeft + _obj.elemDiv.width();
						var newBottom = newTop + _obj.elemDiv.height();


						//set the Maximum values if the new position is outside of the image
						if (!_obj.checkPositions(newTop, newRight, newBottom, newLeft)) {
							!_obj.checkPositionTop(newTop) && (newTop = _obj.max.top);
							!_obj.checkPositionLeft(newLeft) && (newLeft = _obj.max.left);
							!_obj.checkPositionRight(newRight) && (newLeft = _obj.max.right - _obj.elemDiv.width());
							!_obj.checkPositionBottom(newBottom) && (newTop = _obj.max.bottom - _obj.elemDiv.height());

						}
						_obj.elemDiv.css({
							top: newTop + "px",
							left: newLeft + "px"
						}).trigger('positionChange');
						$.Event(event).preventDefault();
					}).on('mouseup.drag', function(event) {
						$(document).off("mousemove.drag");
						$(document).off("mouseup.drag");
						_obj.initializeDragStart();
						_obj.triggerChangeListeners();
						$.Event(event).preventDefault();
					});
					$.Event(event).preventDefault();
					$.Event(event).stopPropagation();
				}).on('positionChange', function() {
					_obj.elemImg.css({
						top: (-1 * $(this).position().top) + "px",
						left: (-1 * $(this).position().left) + "px"
					});
				}).on('sizeChange', function() {
					if (_obj.isLowRes()) {
						_obj.showWarning();
					} else {
						_obj.hideWarning();
					}
				});

				//Resizing span
				this.elemSpan.mousedown(function(event) {
					//remember oldPositions
					_obj.resizeStart.width = _obj.elemDiv.width();
					_obj.resizeStart.height = _obj.elemDiv.height();
					_obj.resizeStart.mouseOffsetTop = event.pageY;
					_obj.resizeStart.mouseOffsetLeft = event.pageX;

					$(document).on('mousemove.resize', function(event) {

						var newWidth = _obj.resizeStart.width - (_obj.resizeStart.mouseOffsetLeft - event.pageX);
						var newHeight = _obj.resizeStart.height - (_obj.resizeStart.mouseOffsetTop - event.pageY);

						if (_obj.fixedRatio) {
							var heightProportion = newHeight / _obj.resizeStart.height;
							var widthProportion = newWidth / _obj.resizeStart.width;
							if (widthProportion >= heightProportion) {
								newHeight = _obj.resizeStart.height * widthProportion;
							} else {
								newWidth = _obj.resizeStart.width * heightProportion;
							}
						}

						var newRight = _obj.getPositionLeft() + newWidth;
						var newBottom = _obj.getPositionTop() + newHeight;

						//check Borders
						if ((!_obj.checkPositionRight(newRight)) || (!_obj.checkPositionBottom(newBottom))) {
							if (!_obj.checkPositionRight(newRight)) {
								newWidth = _obj.elemImg.width() - _obj.getPositionLeft();
								if (_obj.fixedRatio && _obj.checkPositionBottom(newBottom)) {
									newHeight = _obj.resizeStart.height * newWidth / _obj.resizeStart.width;
								}
							}

							if (!_obj.checkPositionBottom(newBottom)) {
								newHeight = _obj.elemImg.height() - _obj.getPositionTop();
								if (_obj.fixedRatio && _obj.checkPositionRight(newRight)) {
									newWidth = _obj.resizeStart.width * newHeight / _obj.resizeStart.height;
								}
							}
						}

						_obj.setSelectorDimensions(newWidth, newHeight);
						event.preventDefault();
					}).on('mouseup.resize', function(event) {
						$(document).off("mousemove.resize");
						$(document).off("mouseup.resize");
						_obj.initializeResizeStart();
						_obj.triggerChangeListeners();
					});
					event.preventDefault();
					event.stopPropagation();
				});
				this.elemDiv.append(this.elemSpan);
				this.initializeMax();
				this.initializeMin();
				this.setSelectorDimensions(this.elemDiv.width(), this.elemDiv.height())
				//first time call, first positionChange & triggering the changeListeners
				this.redraw(this.imageResizer.getSelectedDimension().createResizingDimension());
			}
		}

		private setSelectorDimensions(newWidth: number, newHeight: number) {
			//check MinSize
			if (this.min.width > newWidth) {
				newWidth = this.min.width;
			}

			if (this.min.height > newHeight) {
				newHeight = this.min.height;
			}

			//check MaxSize
			if (this.max.width < newWidth) {
				newWidth = this.max.width;
			}

			if (this.max.height < newHeight) {
				newHeight = this.max.height;
			}

			this.elemDiv.width(newWidth).height(newHeight);
			this.elemDiv.trigger('sizeChange');
		}

		public updateImage() {
			this.elemImg.width(this.imageResizer.getElemImg().width());
			this.elemImg.height(this.imageResizer.getElemImg().height());
			this.initializeMax();
			this.initializeMin();
		}

		public registerChangeListener(changeListener: SizeSelectorListener) {
			this.changeListeners.push(changeListener);
		}
		
		private triggerChangeListeners() {
			this.changeListeners.forEach(function(chnageListener: SizeSelectorListener) {
				chnageListener.onDimensionChange(this);
			}, this);
		}
		
		public redraw(resizingDimension: ResizingDimension) {
			var dimensions = this.imageResizer.determineCurrentDimensions(resizingDimension);
	        this.elemDiv.css({
	            top: dimensions.top + "px",
	            left: dimensions.left + "px"
	        }).width(dimensions.width).height(dimensions.height);
	        this.currentResizingDimension = resizingDimension;
	        this.elemDiv.trigger('positionChange');
	        this.elemDiv.trigger('sizeChange');
	        this.triggerChangeListeners();
	        this.initializeMin();
	        this.initializeMax();
		}
		
		private showWarning() {
    		this.imageResizer.getToolbar().showWarning();
		}
		
		private hideWarning() {
    		this.imageResizer.getToolbar().hideWarning();
		}
	}
	
	class Resizer implements SizeSelectorListener {
		private elemToolbar: JQuery = null;
		private elemContent: JQuery = null;
		public textFixedRatio: string;
		public textLowResolution: string;
		public textZoom: string;
		private dimensions: Array<ResizingDimension> = [];
		private sizeSelector: SizeSelector;
		private toolbar: ResizerToolbar;
		private zoomFactor: number = 1;
		private lastWidth: number = null;
		private originalImageWidth: number = null;
		private originalImageHeight: number = null;
		private selectedDimension: SelectableDimension;
	
		public constructor(private elem: JQuery, private elemDimensionContainer: JQuery, 
				private elemImg: JQuery = null, private maxHeightCheckClosure: () => number = null) {
			if (null === this.elemImg) {
				this.elemImg = $("<img/>").attr("src", elem.attr("data-img-src"));
			}
			
			this.textFixedRatio = elem.data("text-fixed-ratio") || "Fixed Ratio";
	        this.textLowResolution = elem.data("text-low-resolution") || "Low Resolution";
	        this.textZoom = elem.data("text-zoom") || "Zoom";
			
	        this.sizeSelector = new SizeSelector(this, this.elemImg);
	        let firstSelectableDimension: SelectableDimension = null, _obj = this;
			this.elemDimensionContainer.find(".rocket-image-version").each(function() {
				var selectableDimension = new SelectableDimension(_obj, $(this));
				if (null === firstSelectableDimension) {
					firstSelectableDimension = selectableDimension;
				}
			});
			
			if (!this.selectedDimension && firstSelectableDimension) {
				firstSelectableDimension.select();
				this.setSelectedDimension(firstSelectableDimension, false);
			}
			
	        this.sizeSelector.registerChangeListener(this);
	  
	        this.toolbar = new ResizerToolbar(this, this.elemDimensionContainer);
	        
	        this.initializeUi();
		}
		
		public getSelectedDimension() {
			return this.selectedDimension;
		}
		
		public setSelectedDimension(selectedDimension: SelectableDimension, redraw: boolean) {
			this.selectedDimension = selectedDimension;
			if (redraw) {
				let resizingDimension = selectedDimension.createResizingDimension();
	    		this.toolbar.redraw(resizingDimension);
	    		this.sizeSelector.redraw(resizingDimension);
			}
		}
		
		public getToolbar() {
			return this.toolbar;
		}
		
		public getElemContent() {
			return this.elemContent;
		}
		
		public getElemImg() {
			return this.elemImg;
		}
		
		public getElemToolbar() {
			return this.elemToolbar;
		}
		
		public getSizeSelector() {
			return this.sizeSelector;
		}
		
		public getZoomFactor() {
			return this.zoomFactor;
		}
		
		private initializeUi() {
			//Toolbar
	        this.elemToolbar = $("<div/>")
	            .addClass("rocket-image-resizer-toolbar");

	        this.elem.append(this.elemToolbar);

	        //Content
	        this.elemContent = $("<div/>")
	            .addClass("rocket-image-resizer-content")
	            .append($("<div/>").addClass("rocket-image-resizer-content-overlay"));

	        this.elemContent.append(this.elemImg);
	        this.elem.append(this.elemContent);

	        //now it s in tho Document DOM
	        var _obj = this;
	        this.elemImg.on("load", function() {
	            _obj.originalImageWidth = $(this).width();
	            _obj.originalImageHeight = $(this).height();

	            //          if(_obj.elemImg.width() > _obj.elem.width() 
	            //                  || _obj.elemImg.height() > _obj.elem.height()) {
	            //              
	            _obj.applyZoomFactor();

	            _obj.elemImg.width(_obj.originalImageWidth * _obj.zoomFactor);
	            _obj.elemImg.height(_obj.originalImageHeight * _obj.zoomFactor);

	            //          }
	            _obj.initializeUIChildContainers();
	            _obj.elem.on('containerWidthChange', function() {
	                //we need to remember the width and height, it changes after the first width or height change
	                //don't calculate the height -> height isn't responsive
	                _obj.applyZoomFactor();

	                _obj.elemImg.width(_obj.originalImageWidth * _obj.zoomFactor);
	                _obj.elemImg.height(_obj.originalImageHeight * _obj.zoomFactor);

	                _obj.sizeSelector.updateImage();
	                _obj.sizeSelector.redraw(_obj.selectedDimension.createResizingDimension());
	            });
	        });
		}
		
		private applyZoomFactor() {
			let _obj = this,
				accuracy: number = 100000,
				zoomFactorHeight: number = 1,
				zoomFactorWidth: number;
			
	        //Don't Look for the Height
	        if (this.maxHeightCheckClosure !== null) {
	            zoomFactorHeight = (Math.ceil(this.maxHeightCheckClosure() / this.originalImageHeight * accuracy) - 1) / accuracy;
	        }

	        zoomFactorWidth = (Math.ceil(_obj.elem.width() / this.originalImageWidth * accuracy) - 1) / accuracy;

	        if (zoomFactorHeight > zoomFactorWidth) {
	            this.zoomFactor = zoomFactorWidth;
	        } else {
	            this.zoomFactor = zoomFactorHeight;
	        }

	        if (this.zoomFactor !== 1) {
	            this.toolbar.getElemSpanZoom().show().text(this.textZoom + ": " + (this.zoomFactor * 100).toFixed(0) + "%");
	        } else {
	            this.toolbar.getElemSpanZoom().hide();
	        }
		}
		
		private initializeUIChildContainers() {
			let _obj = this;
	        this.toolbar.initializeUi();
	        this.sizeSelector.initializeUI();

	        //redraw with the current dimension
	        this.toolbar.redraw(this.selectedDimension.createResizingDimension());
	        this.lastWidth = this.elem.width();

	        //for the responsive functionality
	        $(window).resize(function() {
	            if (_obj.lastWidth != _obj.elem.width()) {
	                _obj.lastWidth = _obj.elem.width();
	                _obj.elem.trigger('containerWidthChange');
	            }
	        });
		}
		
		public onDimensionChange(sizeSelector: SizeSelector) {
			var _obj = this;

	        var width = sizeSelector.getWidth() / _obj.zoomFactor;
	        if (width > this.originalImageWidth) {
	            width = this.originalImageWidth;
	        }
	        var height = sizeSelector.getHeight() / _obj.zoomFactor;
	        if (height > this.originalImageHeight) {
	            height = this.originalImageHeight;
	        }

	        this.elem.trigger('dimensionChanged', [{
	            left: sizeSelector.getPositionLeft() / _obj.zoomFactor,
	            top: sizeSelector.getPositionTop() / _obj.zoomFactor,
	            width: width,
	            height: height
	        }]);


	        SizeSelectorPositions.addPositions(sizeSelector, _obj.zoomFactor);
		}
		
		public determineCurrentDimensions(resizingDimension: ResizingDimension): SizeSelectorPosition {
			var sizeSelectorPosition = SizeSelectorPositions.getPositions(resizingDimension, this.zoomFactor);
			if (null !== sizeSelectorPosition) return sizeSelectorPosition;
	        var top = 0,
	            left = 0,
	            width = resizingDimension.getWidth(),
	            imageWidth = this.elemImg.width(),
	            height = resizingDimension.getHeight(),
	            imageHeight = this.elemImg.height(),
	            widthExceeded = false,
	            heightExceeded = false,
	            ratio = resizingDimension.getRatio();

	        if (width > imageWidth) {
	            widthExceeded = true;
	            width = imageWidth;
	        } else {
	            left = (imageWidth - width) / 2;
	        }

	        if (height > imageHeight) {
	            height = imageHeight;
	            heightExceeded = true;
	        } else {
	            top = (imageHeight - height) / 2;
	        }

	        if (widthExceeded && heightExceeded) {
	            if ((width / height) > ratio) {
	                widthExceeded = false;
	            } else {
	                heightExceeded = false;
	            }
	        }

	        if (widthExceeded) {
	            height = width / ratio;
	        } else if (heightExceeded) {
	            width = height * ratio;
	        }

	        return new SizeSelectorPosition(left, top, width + 1, height + 1);
		}
	}
	
	class SizeSelectorPositions {
		
		public static addPositions(sizeSelector: SizeSelector, zoomFactor: number): void {
			var currentResizingDimension: ResizingDimension = sizeSelector.getCurrentResizingDimension();
			if (typeof (Storage) === "undefined" || currentResizingDimension === null) return;
			
        	let imageResizerPositions: any;
            if (null == localStorage.imageResizer) {
            	imageResizerPositions = new Object();
            } else {
                imageResizerPositions = JSON.parse(localStorage.imageResizer);
            }
            
            imageResizerPositions[currentResizingDimension.buildStorageKey()] = {
                left: sizeSelector.getPositionLeft() / zoomFactor,
                top: sizeSelector.getPositionTop() / zoomFactor,
                width: sizeSelector.getWidth() / zoomFactor,
                height: sizeSelector.getHeight() / zoomFactor
            }
            
            localStorage.imageResizer = JSON.stringify(imageResizerPositions);
	        
		}
		
		public static getPositions(resizingDimension: ResizingDimension, zoomFactor: number): SizeSelectorPosition {
			if (typeof (Storage) === "undefined" || null == localStorage.imageResizer) return null;
			let imageResizerPositions: any = JSON.parse(localStorage.imageResizer);
			if (!imageResizerPositions[resizingDimension.buildStorageKey()]) return null;
			let jsonObj: any = imageResizerPositions[resizingDimension.buildStorageKey()];
			
			return new SizeSelectorPosition(jsonObj['left'] * zoomFactor, jsonObj['top'] * zoomFactor, 
					jsonObj['width'] * zoomFactor, jsonObj['height'] * zoomFactor);
		}
	}
	
	class SizeSelectorPosition {
		public constructor(public left: number, public top: number, public width: number, public height: number) {
			
		}
	}
	
	export class RocketResizer {
		private resizer: Resizer;
		public constructor(private elem: JQuery) {
			let elemResizer = elem.find("#rocket-image-resizer"),
				elemPageControls = elem.find(".rocket-zone-commands:first"),
				elemRocketheader = $("#rocket-header"),
				elemWindow = $(window),
				elemDimensionContainer = elem.find(".rocket-image-dimensions:first");
			
			this.resizer =  new Resizer(elemResizer, elemDimensionContainer, null, function() {
				var height = elemWindow.height() - 50;
				if (elemRocketheader.length > 0) {
					height -= elemRocketheader.outerHeight();
				}
				
				if (elemPageControls.length > 0) {
					height -= elemPageControls.outerHeight();
				}
				
				return height;
			});
			
			let elemInpPositionX = elem.find("#rocket-thumb-pos-x").hide(),
				elemInpPositionY = elem.find("#rocket-thumb-pos-y").hide(),
				elemInpWidth = elem.find("#rocket-thumb-width").hide(),
				elemInpHeight = elem.find("#rocket-thumb-height").hide();
			
			
			elem.find(".rocket-image-version > img").each(function() {
				$(this).attr('src', $(this).attr("src") + "?timestamp=" + new Date().getTime());
			});
				
			elemResizer.on('dimensionChanged', function(event, dimension) {
				elemInpPositionX.val(Math.floor(dimension.left));
				elemInpPositionY.val(Math.floor(dimension.top));
				elemInpWidth.val(Math.floor(dimension.width));
				elemInpHeight.val(Math.floor(dimension.height));
			});
		}
	}
}