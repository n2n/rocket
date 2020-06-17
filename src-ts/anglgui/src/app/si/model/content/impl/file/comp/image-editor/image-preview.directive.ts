import { Directive, Input, ElementRef, OnInit, OnDestroy } from '@angular/core';
import { SiImageDimension } from '../../model/file-in-si-field';
import { ThumbRatio } from './image-editor.component';

@Directive({
	selector: '[rocketImagePreview]'
})
export class ImagePreviewDirective implements OnInit, OnDestroy {

	@Input()
	thumbRatio: ThumbRatio;
	@Input()
	imageDimension: SiImageDimension|null = null;

	constructor(private elemRef: ElementRef) { }

	ngOnInit() {
		this.thumbRatio.registerPreviewImg(this.elemRef, this.imageDimension);
	}

	ngOnDestroy() {
		this.thumbRatio.unregisterPreviewImg(this.elemRef);
	}

}
