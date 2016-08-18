jQuery(window).ready(function($) {
	
	var HnmImageResizingDimension = function(dimensionString, zoomFactor) {
		//not possible in js
		//this.dimensionSplitPattern = new RegExp("(x|(?<=\d)(?=crop))");
		this.zoomFactor = zoomFactor || 1;
		this.dimensionMatchPattern = new RegExp("\\d+x\\d+[xcrop]?");
		this.dimensionString = dimensionString;
		this.width = 0;
		this.height = 0;
		this.crop = false;
		this.ratio = 1;
		this.initialize();
	};

	HnmImageResizingDimension.prototype.initialize = function() {
		if (this.dimensionString.match(this.dimensionMatchPattern) === null) return;
		var dimension = this.dimensionString.split("x");
		this.width = dimension[0] * this.zoomFactor;
		this.height = dimension[1] * this.zoomFactor;
		this.crop = dimension.length > 2;
		this.ratio = this.width / this.height;
	};

	var HnmImageResizerToolbar = function(imageResizer, jqElemSelect) {
		this.imageResizer = imageResizer;
		
		this.jqElemUl = null;
		this.jqElemSelect = jqElemSelect;
		this.jqElemCbxFixedRatio = null;
		this.jqElemLiFixedRatio = null;
		this.jqElemSpanWarning = null;
		this.jqElemSpanZoom = $("<span/>");
	};

	HnmImageResizerToolbar.prototype.initializeUI = function() {
		var _obj = this;
		
		this.jqElemUl = $("<ul/>").css({
			margin: "0px"
		});
		
		this.jqElemSelect.change(function() {
			_obj.redraw();
		});
		this.jqElemUl.append($("<li/>").append(this.jqElemSelect));
		
		var randomId = "rocket-image-resizer-fixed-ratio-" + parseInt(Math.random() * 10000);
		
		this.jqElemLiFixedRatio = $("<li/>").addClass("rocket-fixed-ratio").append(
						$("<label/>").attr("for", randomId).css("display", "inline-block").text(_obj.imageResizer.textFixedRatio));
		this.jqElemCbxFixedRatio = $("<input type='checkbox'/>").addClass("rocket-image-resizer-fixed-ratio").attr("id", randomId)
			.change(function() {
				var imageSizeSelector = _obj.imageResizer.imageSizeSelector;
				if ($(this).prop("checked")) {
					imageSizeSelector.fixedRatio = true;
					imageSizeSelector.checkRatio();
				} else {
					imageSizeSelector.fixedRatio = false;
				}
				imageSizeSelector.initializeMin();
				imageSizeSelector.initializeMax();
			});
		
		
		this.jqElemLiFixedRatio.append(this.jqElemCbxFixedRatio);
		this.jqElemUl.append(this.jqElemLiFixedRatio);
		
		this.jqElemSpanWarning = $("<span/>").addClass("rocket-image-resizer-warning").text(this.imageResizer.textLowResolution).hide();
		this.jqElemUl.append($("<li/>").addClass("rocket-low-resolution").append(this.jqElemSpanWarning));
		this.jqElemUl.append($("<li/>").append(this.jqElemSpanZoom));
		
		this.imageResizer.jqElemToolbar.append(this.jqElemUl);
	};
	
	HnmImageResizerToolbar.prototype.updateDimension = function() {
		var imageResizingDimension = new HnmImageResizingDimension(this.jqElemSelect.val(), this.imageResizer.zoomFactor);
		if (imageResizingDimension) {
			this.imageResizer.redraw(imageResizingDimension);
		}
	};

	HnmImageResizerToolbar.prototype.redraw = function() {
		var imageResizingDimension = new HnmImageResizingDimension(this.jqElemSelect.val(), this.imageResizer.zoomFactor);
		if (imageResizingDimension) {
			if (imageResizingDimension.crop) {
				this.jqElemCbxFixedRatio.prop("checked", true);
				this.jqElemLiFixedRatio.hide();
				this.jqElemCbxFixedRatio.trigger("change");
			} else {
				this.jqElemCbxFixedRatio.prop("checked", true);
				this.jqElemCbxFixedRatio.trigger("change");
				this.jqElemLiFixedRatio.show();
			}
			this.imageResizer.redraw(imageResizingDimension);
		};
	};

	var HnmImageSizeSelector = function(imageResizer, jqElemImg) {
		
		this.imageResizer = imageResizer;
		this.fixedRatio = false;
		this.currentDimension = null;
		
		this.jqElemImg = jqElemImg;
		this.jqElemDiv = null;
		this.jqElemSpan = null;
		this.imageLoaded = false;
		
		var _obj = this;
		
		//dragging
		this.dragStart = null;
		
		//resizing
		this.resizeStart = null;
		
		//maximums
		this.max = null;
		
		//minimums
		this.min = null;
		
		this.positionTop = function() {
			return _obj.jqElemDiv.position().top;
		};
		
		this.positionLeft = function() {
			return _obj.jqElemDiv.position().left;
		};
		
		this.width = function() {
			return _obj.jqElemDiv.width();
		};
		
		this.height = function() {
			return _obj.jqElemDiv.height();
		};
		
		this.changeListeners = new Array();
		
		this.initialize();
	};
	
	HnmImageSizeSelector.prototype.initialize = function() {
		this.initializeResizeStart();
		this.initializeDragStart();
	};
	
	HnmImageSizeSelector.prototype.checkRatio = function() {
		if (!this.fixedRatio || !this.currentDimension) return
		var width = this.jqElemDiv.width();
		var height = this.jqElemDiv.height();
		if (width < height) {
			this.jqElemDiv.height(width / this.currentDimension.ratio);
		} else {
			this.jqElemDiv.width(height * this.currentDimension.ratio);
		}
		this.jqElemDiv.trigger('sizeChange');
	};
	
	HnmImageSizeSelector.prototype.initializeMin = function() {
		var spanHeight = this.jqElemSpan.height();
		if (this.fixedRatio && null !== this.currentDimension) {
			var ratio = this.currentDimension.width / this.currentDimension.height;
			if (this.currentDimension.width > this.currentDimension.height) {
				this.min = {
					width: spanHeight * ratio,
					height: spanHeight
				};
			} else {
				this.min = {
					width: spanHeight,
					height: spanHeight / ratio
				};
			}
		} else {
			this.min = {
				width: this.jqElemSpan.width(),
				height: this.jqElemSpan.height()
			};
		}
	};
	
	HnmImageSizeSelector.prototype.initializeMax = function() {
		var imageWidth = this.jqElemImg.width();
		var imageHeight = this.jqElemImg.height();
		this.max = {
			top: 0,
			left: 0,
			right: imageWidth,
			bottom: imageHeight
		}
		if (this.fixedRatio && null !== this.currentDimension) {
			var ratio = this.currentDimension.width / this.currentDimension.height;
			this.max.width = imageHeight * ratio;
			if (this.max.width > imageWidth) {
				this.max.width = imageWidth;
			}
			this.max.height = imageWidth / ratio;
			if (this.max.height > imageHeight) {
				this.max.height = imageHeight;
			}
		} else {
			this.max.width = imageWidth;
			this.max.height = imageHeight;
		}
		
	};
	
	HnmImageSizeSelector.prototype.initializeDragStart = function() {
		this.dragStart = {
			positionTop: null,
			positionLeft: null,
			mouseOffsetTop: null,
			mouseOffsetLeft: null
		};
	};
	
	HnmImageSizeSelector.prototype.initializeResizeStart = function() {
		this.resizeStart = {
			width: null,
			height: null, 
			mouseOffsetTop: null,
			mouseOffsetLeft: null
		};
	};
	
	HnmImageSizeSelector.prototype.checkPositionRight = function(newRight) {
		return this.max.right > newRight;
	};
	
	HnmImageSizeSelector.prototype.checkPositionLeft = function(newLeft) {
		return this.max.left < newLeft;
	};
	
	HnmImageSizeSelector.prototype.checkPositionBottom = function(newBottom) {
		return this.max.bottom > newBottom;
	};
	
	HnmImageSizeSelector.prototype.checkPositionTop = function(newTop) {
		return (this.max.top < newTop);
	};
	
	HnmImageSizeSelector.prototype.checkPositions = function(newTop, newRight, newBottom, newLeft) {
		return this.checkPositionTop(newTop) && this.checkPositionRight(newRight)
				&& this.checkPositionBottom(newBottom) && this.checkPositionLeft(newLeft);
	};

	HnmImageSizeSelector.prototype.initializeUI = function() {
		var _obj = this;
		if (!this.imageLoaded) {
			this.jqElemDiv = $("<div/>").css({
				zIndex: 100,
				position: "absolute",
				overflow: "hidden"
			}).addClass("rocket-image-resizer-size-selector");
			
			//Image
			this.jqElemImg = $("<img/>").css("position", "relative");
			
			this.jqElemImg.load(function() {
				_obj.imageLoaded = true;
				_obj.initializeUI();
			}).attr("src", this.imageResizer.jqElemImg.attr("src"));			
			
			this.jqElemDiv.append(this.jqElemImg);
			
			this.imageResizer.jqElemContent.append(this.jqElemDiv);
			
			this.jqElemSpan = $("<span/>").css({
				zIndex: 101,
				position: "absolute",
				right: "-1px",
				bottom: "-1px"
			});
		} else {
			this.imageResizer.jqElemContent.css({
				position: "relative"
			});
			this.jqElemImg.width(this.imageResizer.jqElemImg.width()).height(this.imageResizer.jqElemImg.height());
			
			var resizerDragStartFunc = function ( event ) {
				//remember oldPositions
				$.Event(event).preventDefault();
				$.Event(event).stopPropagation();
				
				_obj.dragStart.positionTop = _obj.jqElemDiv.position().top;
				_obj.dragStart.positionLeft = _obj.jqElemDiv.position().left;
				
				_obj.dragStart.mouseOffsetTop = _obj.determinePageY(event);
				_obj.dragStart.mouseOffsetLeft = _obj.determinePageX(event);
				
				var resizerDragMoveFunc = function( event ) {
					//var borderWidth = (_obj.jqElemDiv.outerWidth() - _obj.jqElemDiv.innerWidth()) / 2
					
					var newTop = _obj.dragStart.positionTop - (_obj.dragStart.mouseOffsetTop - _obj.determinePageY(event));
					var newLeft = _obj.dragStart.positionLeft - (_obj.dragStart.mouseOffsetLeft - _obj.determinePageX(event));
					var newRight = newLeft + _obj.jqElemDiv.width();
					var newBottom = newTop + _obj.jqElemDiv.height();
					
	
					//set the Maximum values if the new position is outside of the image
					if (!_obj.checkPositions(newTop, newRight, newBottom, newLeft)) {
						!_obj.checkPositionTop(newTop) && (newTop = _obj.max.top);
						!_obj.checkPositionLeft(newLeft) && (newLeft = _obj.max.left);
						!_obj.checkPositionRight(newRight) && (newLeft = _obj.max.right - _obj.jqElemDiv.width());
						!_obj.checkPositionBottom(newBottom) && (newTop = _obj.max.bottom - _obj.jqElemDiv.height());
						
					}
					_obj.jqElemDiv.css({
						top: newTop + "px",
						left: newLeft + "px"
					}).trigger('positionChange');
					$.Event(event).preventDefault();
				};
				
				var resizerDragEndFunc = function (event) {
					$(document).off("mousemove.drag").off("touchmove.drag");
					$(document).off("mouseup.drag").off("touchend.drag");
					_obj.initializeDragStart();
					_obj.triggerChangeListeners();
					$.Event(event).preventDefault();
				};
				
				$(document).on({
					'mousemove.drag': resizerDragMoveFunc, 
					'touchmove.drag': function(e) {
						resizerDragMoveFunc(e);
					},
					'mouseup.drag': resizerDragEndFunc, 
					'touchend.drag': resizerDragEndFunc
				});
			};
			
			this.jqElemDiv.on({
				"mousedown": resizerDragStartFunc,
				"touchstart": resizerDragStartFunc
			}).on('positionChange', function() {
				_obj.jqElemImg.css({
					top: (-1 * $(this).position().top) + "px",
					left: (-1 * $(this).position().left) + "px"
				});
			}).on('sizeChange', function() {
				if (_obj.currentDimension) {
					if (_obj.currentDimension.width > (_obj.width() + 1) 
							|| _obj.currentDimension.height > (_obj.height() + 1)) {
						_obj.showWarning();
						return;
					}
				}
				_obj.hideWarning();
				
			});
			
			var resizerResizeMoveFunc = function ( event ) {
				//remember oldPositions
				_obj.resizeStart.width = _obj.jqElemDiv.width();
				_obj.resizeStart.height = _obj.jqElemDiv.height();
				_obj.resizeStart.mouseOffsetTop = _obj.determinePageY(event);
				_obj.resizeStart.mouseOffsetLeft = _obj.determinePageX(event);
				
				event.preventDefault();
				event.stopPropagation();
				
				var resizerResizeMoveFunc = function( event ) {
					var newWidth = _obj.resizeStart.width - ( _obj.resizeStart.mouseOffsetLeft - _obj.determinePageX(event));
					var newHeight = _obj.resizeStart.height - ( _obj.resizeStart.mouseOffsetTop - _obj.determinePageY(event));
					
					if (_obj.fixedRatio) {
						var heightProportion = newHeight / _obj.resizeStart.height;
						var widthProportion = newWidth / _obj.resizeStart.width;
						if (widthProportion >= heightProportion) {
							newHeight = _obj.resizeStart.height * widthProportion;
						} else {
							newWidth = _obj.resizeStart.width * heightProportion;
						}
					}

					var newRight = _obj.positionLeft() + newWidth;
					var newBottom = _obj.positionTop() + newHeight;
					
					//check Borders
					if ((!_obj.checkPositionRight(newRight)) || (!_obj.checkPositionBottom(newBottom))) {
						if (!(_obj.checkPositionRight(newRight))) {
							newWidth = _obj.jqElemImg.width() - _obj.positionLeft();
							if (_obj.fixedRatio && _obj.checkPositionBottom(newBottom)) {
								newHeight = _obj.resizeStart.height * newWidth / _obj.resizeStart.width;
							}
						} 
						
						if (!(_obj.checkPositionBottom(newBottom))) {
							newHeight = _obj.jqElemImg.height() - _obj.positionTop();
							if (_obj.fixedRatio && _obj.checkPositionRight(newRight)) {
								newWidth = _obj.resizeStart.width * newHeight / _obj.resizeStart.height;
							}
						}
						
						if (!(_obj.checkPositionRight(newRight)) && !(_obj.checkPositionBottom(newBottom))) {
							if (_obj.fixedRatio) {
								if (widthProportion >= heightProportion) {
									newHeight = _obj.resizeStart.height * newWidth / _obj.resizeStart.width;
								} else {
									newWidth = _obj.resizeStart.width * newHeight / _obj.resizeStart.height;
								}
							}
						}
					} 
					
					_obj.setSelectorDimensions(newWidth, newHeight);
					event.preventDefault();
				};
				
				var resizerResizeEndFunc = function(e) {
					$(document).off("mousemove.resize").off("touchmove.resize")
							.off("mouseup.resize").off("touchend.resize");
					_obj.initializeResizeStart();
					_obj.triggerChangeListeners();
				};
				
				$(document).on({
					'mousemove.resize': resizerResizeMoveFunc,
					'touchmove.resize': resizerResizeMoveFunc,
					'mouseup.resize': resizerResizeEndFunc, 
					'touchend.resize': resizerResizeEndFunc
				});
			};
			
			//Resizing span
			this.jqElemSpan.on({
				'mousedown': resizerResizeMoveFunc,
				'touchstart': resizerResizeMoveFunc
			});
			this.jqElemDiv.append(this.jqElemSpan);
			this.initializeMax();
			this.initializeMin();
			this.setSelectorDimensions(this.jqElemDiv.width(), this.jqElemDiv.height())
			//first time call, first positionChange & triggering the changeListeners
			this.jqElemDiv.trigger('positionChange');
			_obj.triggerChangeListeners();
		}
	};
	
	HnmImageSizeSelector.prototype.determinePageY = function(event) {
		return (null != event.pageY) ? event.pageY : event.originalEvent.touches[0].pageY
	};
	
	HnmImageSizeSelector.prototype.determinePageX = function(event) {
		return (null != event.pageX) ? event.pageX : event.originalEvent.touches[0].pageX
	};
	
	HnmImageSizeSelector.prototype.setSelectorDimensions = function(newWidth, newHeight) {
		//check MinSize
		(this.min.width > newWidth) && (newWidth = this.min.width);
		(this.min.height > newHeight) && (newHeight = this.min.height);
		
		//check MaxSize
		(this.max.width < newWidth) && (newWidth = this.max.width);
		(this.max.height < newHeight) && (newHeight = this.max.height);
		
		this.jqElemDiv.width(newWidth)
				.height(newHeight);
		
		this.jqElemDiv.trigger('sizeChange');
	}
	
	HnmImageSizeSelector.prototype.updateImage = function() {
		this.jqElemImg.width(this.imageResizer.jqElemImg.width());
		this.jqElemImg.height(this.imageResizer.jqElemImg.height());
		this.initializeMax();
		this.initializeMin();
	};

	HnmImageSizeSelector.prototype.registerChangeListener = function(changeListener) {
		if ($.isFunction(changeListener.onDimensionChange)) {
			this.changeListeners.push(changeListener);
		}
	};

	HnmImageSizeSelector.prototype.triggerChangeListeners = function () {
		for (var i in this.changeListeners) {
			this.changeListeners[i].onDimensionChange(this);
		}
	};

	HnmImageSizeSelector.prototype.redraw = function(imageResizingDimension) {
		var dimensions = this.imageResizer.determineCurrentDimensions(imageResizingDimension);
		this.jqElemDiv.css({
			top: dimensions.top + "px",
			left: dimensions.left + "px"
		}).width(dimensions.width).height(dimensions.height);
		this.currentDimension = imageResizingDimension;
		this.jqElemDiv.trigger('positionChange');
		this.jqElemDiv.trigger('sizeChange');
		this.triggerChangeListeners();
		this.initializeMin();
		this.initializeMax();
	};
	
	HnmImageSizeSelector.prototype.showWarning = function() {
		this.imageResizer.toolbar.jqElemSpanWarning.show();
	};
	
	HnmImageSizeSelector.prototype.hideWarning = function() {
		this.imageResizer.toolbar.jqElemSpanWarning.hide();
	};

	var HnmImageResizer = function(jqElem, jqElemSelectDimensions, jqElemImg, maxHeightCheckClosure) {
		this.jqElem = jqElem;
		
		this.jqElemSelectDimensions = jqElemSelectDimensions || $(jqElem.attr("data-dimension-select-selector")); 
		this.jqElemImg = jqElemImg || $("<img/>").attr("src", jqElem.attr("data-img-src"));
		
		this.jqElemToolbar = null;
		this.jqElemContent = null;
		
		this.textFixedRatio = jqElem.data("text-fixed-ratio") || "Fixed Ratio";
		this.textLowResolution = jqElem.data("text-low-resolution") || "Low Resolution";
		this.textZoom = jqElem.data("text-zoom") || "Zoom";
		
		this.dimensions = new Array();

		this.imageSizeSelector = new HnmImageSizeSelector(this, this.jqElemImg);
		this.imageSizeSelector.registerChangeListener(this);
		this.toolbar = new HnmImageResizerToolbar(this, this.jqElemSelectDimensions);
		
		this.zoomFactor = 1;
		this.lastWidth = null;
		
		this.originalImageWidth = null;
		this.originalImageHeight = null;
		
		this.maxHeightCheckClosure = maxHeightCheckClosure || null;
		
		this.initialize();
	};

	HnmImageResizer.prototype.initialize = function() {
		this.initializeUI();
	};

	HnmImageResizer.prototype.initializeUI = function() {
		
		//Toolbar
		this.jqElemToolbar = $("<div/>")
				.addClass("rocket-image-resizer-toolbar");
		
		this.jqElem.append(this.jqElemToolbar);
		
		//Content
		this.jqElemContent = $("<div/>")
				.addClass("rocket-image-resizer-content")
				.append($("<div/>").addClass("rocket-image-resizer-content-overlay"));
		
		this.jqElemContent.append(this.jqElemImg);
		
		this.jqElem.append(this.jqElemContent);
		
		//now it s in tho Document DOM
		var _obj = this;
		this.jqElemImg.load(function() {
			_obj.originalImageWidth = $(this).width();
			_obj.originalImageHeight = $(this).height();
			
//			if(_obj.jqElemImg.width() > _obj.jqElem.width() 
//					|| _obj.jqElemImg.height() > _obj.jqElem.height()) {
//				
				_obj.applyZoomFactor();
				
				_obj.jqElemImg.width(_obj.originalImageWidth * _obj.zoomFactor);
				_obj.jqElemImg.height(_obj.originalImageHeight * _obj.zoomFactor);
				
//			}
			_obj.initializeUIChildContainers();
			_obj.jqElem.on('containerWidthChange', function() {
				//we need to remember the width and height, it changes after the first width or height change
				//don't calculate the height -> height isn't responsive
				_obj.applyZoomFactor();
				
				_obj.jqElemImg.width(_obj.originalImageWidth * _obj.zoomFactor);
				_obj.jqElemImg.height(_obj.originalImageHeight * _obj.zoomFactor);
				
				_obj.imageSizeSelector.updateImage();
				_obj.toolbar.updateDimension();
			});
		})
	};
	
	HnmImageResizer.prototype.applyZoomFactor = function() {
		var _obj = this;
		var calculateHeight = calculateHeight || false; 
		var accuracy = 100000;
		var zoomFactorHeight = 1;
		//Don't Look for the Height
		if (this.maxHeightCheckClosure !== null) {
			var zoomFactorHeight = (Math.ceil(this.maxHeightCheckClosure() / this.originalImageHeight * accuracy) - 1) / accuracy;
		}
		
		var zoomFactorWidth = (Math.ceil(_obj.jqElem.width() / this.originalImageWidth * accuracy) - 1) / accuracy;
		
		if (zoomFactorHeight > zoomFactorWidth) {
			this.zoomFactor = zoomFactorWidth;
		} else {
			this.zoomFactor = zoomFactorHeight;
		}
		
		if (this.zoomFactor.toFixed(2) != 1) {
			this.toolbar.jqElemSpanZoom.show().text(this.textZoom + ": " + (this.zoomFactor * 100).toFixed(0) + "%");
		} else {
			this.toolbar.jqElemSpanZoom.hide();
		}
	};
	
	HnmImageResizer.prototype.initializeUIChildContainers = function() {
		var _obj = this;
		this.toolbar.initializeUI();
		this.imageSizeSelector.initializeUI();
		
		//redraw with the current dimension
		this.toolbar.redraw();
		this.lastWidth = this.jqElem.width();
		
		//for the responsive functionality
		$(window).resize(function() {
			if(_obj.lastWidth != _obj.jqElem.width()) {
				_obj.lastWidth = _obj.jqElem.width();
				_obj.jqElem.trigger('containerWidthChange');
			}
		});
	};

	HnmImageResizer.prototype.redraw = function(imageResizingDimension) {
		this.imageSizeSelector.redraw(imageResizingDimension);
	};

	HnmImageResizer.prototype.onDimensionChange = function(imageSizeSelector) {
		var _obj = this;
		
		var width = imageSizeSelector.width() / _obj.zoomFactor;
		if (width > this.originalImageWidth) {
			width = this.originalImageWidth;
		}
		var height = imageSizeSelector.height() / _obj.zoomFactor;
		if (height > this.originalImageHeight) {
			height = this.originalImageHeight;
		}

		this.jqElem.trigger('dimensionChanged', [{
			left: imageSizeSelector.positionLeft() / _obj.zoomFactor,
			top: imageSizeSelector.positionTop() / _obj.zoomFactor,
			width: width,
			height: height
		}]);
		
		
		if(typeof(Storage) !== "undefined" && null !== imageSizeSelector.currentDimension) {
			if (null == localStorage.imageResizer) {
				imageResizerPositions = new Object();
			} else {
				imageResizerPositions = JSON.parse(localStorage.imageResizer);
			}
			imageResizerPositions[location.href + '/' + imageSizeSelector.currentDimension.dimensionString] = {
				left: imageSizeSelector.positionLeft() / _obj.zoomFactor,
				top: imageSizeSelector.positionTop() / _obj.zoomFactor,
				width: imageSizeSelector.width() / _obj.zoomFactor,
				height:  imageSizeSelector.height() / _obj.zoomFactor
			}
			localStorage.imageResizer = JSON.stringify(imageResizerPositions);
		}
	};
	
	HnmImageResizer.prototype.determineCurrentDimensions = function(imageDimension) {
		var top = 0, 
			left = 0,
			width = imageDimension.width,
			imageWidth = this.jqElemImg.width(),
			height = imageDimension.height,
			imageHeight =  this.jqElemImg.height(), 
			widthExceeded = false,
			heightExceeded = false;
		
		if (typeof(Storage) !== "undefined" && null != localStorage.imageResizer) {
			imageResizerPositions = JSON.parse(localStorage.imageResizer);
			if (null != imageResizerPositions[location.href + '/' + imageDimension.dimensionString]) {
				 var positions = imageResizerPositions[location.href + '/' + imageDimension.dimensionString];
				 for (var i in positions) {
					 positions[i] = positions[i] * this.zoomFactor;
				 }
				 
				 //check position borders
				 if (((positions['top'] + positions['height']) < imageHeight) 
						 && ((positions['left'] + positions['width']) < imageWidth)) {
					 return positions;
				 }
			}
		} 
		
		
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
			if ((width / height) > imageDimension.ratio) {
				widthExceeded = false;
			} else {
				heightExceeded = false;
			}
		}
		
		if (widthExceeded) {
			height = width / imageDimension.ratio;
		} else if (heightExceeded) {
			width = height * imageDimension.ratio;
		}
		
		return {
			applyzoomFactor: false,
			left: left,
			top: top,
			width: width + 1,
			height: height + 1
		};
	}
	window.HnmImageResizer = HnmImageResizer;
});