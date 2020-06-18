import { Directive, Input, ElementRef, OnInit, OnDestroy } from '@angular/core';
import { SiImageDimension } from '../../model/file-in-si-field';
import { ThumbRatio, ImageSrc } from './image-editor.component';

@Directive({
	selector: '[rocketImagePreview]'
})
export class ImagePreviewDirective implements OnInit, OnDestroy {

	@Input()
	imageSrc: ImageSrc;
	@Input()
	thumbRatio: ThumbRatio;
	@Input()
	imageDimension: SiImageDimension|null = null;

	constructor(private elemRef: ElementRef) { }

	ngOnInit() {
		// this.imageSrc.ready$.subscribe(() => {
		// 	this.elemRef.nativeElement.setAttribute('src', this.imageSrc.createPreviewDataUrl(this.thumbRatio.getGroupedImageCut()));
		// });

		// this.thumbRatio.registerPreviewImg(this.elemRef, this.imageDimension);
	}

	ngOnDestroy() {
		// this.thumbRatio.unregisterPreviewImg(this.elemRef);
	}



}
