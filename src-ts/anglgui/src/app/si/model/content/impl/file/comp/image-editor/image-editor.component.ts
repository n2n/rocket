import { Component, OnInit, ViewChild, ElementRef, AfterViewInit, QueryList, ViewChildren } from '@angular/core';
import Cropper from 'cropperjs';
import { SiFile, SiImageDimension, SiImageCut } from '../../model/file-in-si-field';
import { ImageEditorModel } from '../image-editor-model';
import { ImagePreviewDirective } from './image-preview.directive';

@Component({
	selector: 'rocket-image-editor',
	templateUrl: './image-editor.component.html',
	styleUrls: ['./image-editor.component.css']
})
export class ImageEditorComponent implements OnInit, AfterViewInit {

	model: ImageEditorModel;

	@ViewChild('img', { static: true })
	canvasRef: ElementRef;

	@ViewChild('originalPreview', { static: true })
	originalPreviewRef: ElementRef;

	@ViewChildren(ImagePreviewDirective)
	imagePreviewQueryList: QueryList<ImagePreviewDirective>;

	private imageSrc: ImageSrc;
	cropping = false;

	private ratioMap = new Map<string, ThumbRatio>();

	private currentThumbRatio: ThumbRatio|null = null;
	private originalChanged = false;

	constructor() { }

	ngOnInit() {
		this.imageSrc = new ImageSrc(this.canvasRef);
	}

	ngAfterViewInit() {
	// 		viewMode: 1,
	// 		crop(event) {
	// 			// console.log(event.type);
	// 			// console.log(event.detail.x);
	// 			// console.log(event.detail.y);
	// 			// console.log(event.detail.width);
	// 			// console.log(event.detail.height);
	// 			// console.log(event.detail.rotate);
	// 			// console.log(event.detail.scaleX);
	// 			// console.log(event.detail.scaleY);
	// 		},
	// 		ready() {
	// 			console.log(this.cropper.getCanvasData());
	// 			console.log(this.cropper.getContainerData());
	// 			// this.cropper.setCropBoxData({ x: 0, y: 0, width: this.cropper.width, height: this.cropper.height,
	// 			// 		rotate: 0, scaleX: 1, scaleY: 1 });

	// 			// this.cropper.setCropBoxData(this.cropper.getCanvasData());
	// 			this.cropper.clear();
	// 		}
	// 	});

		this.imageSrc.init(null, this.originalPreviewRef.nativeElement);

		this.initSiFile(this.model.getSiFile());
	}

	get thumbRatio() {
		return this.currentThumbRatio;
	}

	get originalActive(): boolean {
		return !this.currentThumbRatio;
	}

	switchToOriginal() {
		this.currentThumbRatio = null;
	}

	ensureOriginalUnchanged() {
		return this.originalChanged;
	}

	switchToThumbRatio(thumbRatio: ThumbRatio) {
		this.cropper.crop();

		this.currentThumbRatio = thumbRatio;

		const imageCut = thumbRatio.baseImageCut;
		this.cropper.setCropBoxData({ left: imageCut.x, top: imageCut.y, width: imageCut.width,
				height: imageCut.height });
	}

	switchToImageDimension(thumbRatio: ThumbRatio, imageDimension: SiImageDimension) {

	}

	toggleCropping() {
		this.cropping = !this.cropping;

		if (this.cropping) {
			this.cropper.crop();
		} else {
			this.cropper.clear();
		}
	}

	private initSiFile(siFile: SiFile) {
		this.ratioMap.clear();

		for (const imageDimension of siFile.imageDimensions) {
			const thumbRatio = ThumbRatio.create(imageDimension);
			const ratio = (thumbRatio.width / thumbRatio.height) + (thumbRatio.ratioFixed ? 'f' : '');

			if (!this.ratioMap.has(ratio)) {
				this.ratioMap.set(ratio, thumbRatio);
				continue;
			}

			this.ratioMap.get(ratio).addImageDimension(imageDimension);
		}
	}

	get thumbRatios(): ThumbRatio[] {
		return Array.from(this.ratioMap.values());
	}

	dingsel() {
		this.cropper.getCroppedCanvas().toDataURL('image/jpeg');
	}

	save() {
		// this.cropper.getCroppedCanvas().toBlob();
	}
}

export class ImageSrc {
	private cropper: Cropper|null = null;

	constructor(private elemRef: ElementRef) {
	}

	init(imageCut: SiImageCut|null, previewElem: Element) {
		this.reset();

		this.cropper = new Cropper(this.elemRef.nativeElement, {
			viewMode: 1,
			preview: previewElem,
			crop(event) {
				// console.log(event.type);
				// console.log(event.detail.x);
				// console.log(event.detail.y);
				// console.log(event.detail.width);
				// console.log(event.detail.height);
				// console.log(event.detail.rotate);
				// console.log(event.detail.scaleX);
				// console.log(event.detail.scaleY);
			},
			ready() {
				console.log(this.cropper.getCanvasData());
				console.log(this.cropper.getContainerData());

				if (imageCut) {
					this.cropper.setCropBoxData({ left: imageCut.x, top: imageCut.y, width: imageCut.width, 
						height: imageCut.height });
				// 		rotate: 0, scaleX: 1, scaleY: 1 });
				}
				// this.cropper.setCropBoxData(this.cropper.getCanvasData());
				this.cropper.clear();
			}
		});
	}

	reset() {
		if (!this.cropper) {
			return;
		}

		this.cropper.destroy();
		this.cropper = null;
	}

	rotateCw() {
		this.cropper.rotate(90);
	}

	rotateCcw() {
		this.cropper.rotate(-90);
	}

}

export class ThumbRatio {
	public imageDimensions = new Array<SiImageDimension>();
	// private _largestImageDimension: SiImageDimension;

	private imgMap = new Map<string, SiImageDimension[]>();

	public baseImageCut: SiImageCut;

	constructor(readonly width: number, readonly height: number, readonly ratioFixed = false) {
	}

	static create(imageDimension: SiImageDimension): ThumbRatio {
		const width = imageDimension.width;
		const height = imageDimension.height;
		const ggt = ThumbRatio.gcd(width, height);

		const thumbRatio = new ThumbRatio(width / ggt, height / ggt, imageDimension.ratioFixed);
		thumbRatio.addImageDimension(imageDimension);
		return thumbRatio;
	}

	private static gcd(num1: number, num2: number): number {
		if (num2 === 0) {
			return num1;
		}

		return ThumbRatio.gcd(num2, num1 % num2);
	}

	registerPreviewImg(htmlElement: HTMLElement, imageDimension: SiImageDimension|null) {
	}

	unregisterPreviewImg(htmlElement: HTMLElement) {
	}

	get label(): string {
		return this.width + ' / ' + this.height;
	}

	// get largestImageDimension(): SiImageDimension {
	// 	return this._largestImageDimension;
	// }

	// get customRatio(): number {
	// 	return this.width / this.height;
	// }

	addImageDimension(imageDimension: SiImageDimension) {
		this.imageDimensions.push(imageDimension);

		// if (!this._largestImageDimension || this._largestImageDimension.height < imageDimension.height) {
		// 	this._largestImageDimension = imageDimension;
		// }

		this.classifyImageDimension(imageDimension);
	}

	private recalcImgMap() {
		this.imgMap.clear();

		for (const imageDimension of this.imageDimensions) {
			this.classifyImageDimension(imageDimension);
		}

		this.determineGroupedImageCut();
	}

	private classifyImageDimension(imageDimension: SiImageDimension) {
		const key = this.imgCutKey(imageDimension.imageCut);
		if (!this.imgMap.has(key)) {
			this.imgMap.set(key, []);
		}

		this.imgMap.get(key).push(imageDimension);
	}

	private determineGroupedImageCut() {
		this.baseImageCut = null;

		let lastSize = 0;
		for (const [key, imgDims] of this.imgMap) {
			if (lastSize >= imgDims.length || imgDims.length > 1) {
				continue;
			}

			this.baseImageCut = imgDims[0].imageCut;
			lastSize = imgDims.length;
		}
	}

	private imgCutKey(imgCut: SiImageCut): string {
		return imgCut.width + ',' + imgCut.height + ',' + imgCut.x + ',' + imgCut.y;
	}

	hasBaseImageCut(): boolean {
		return !!this.baseImageCut;
	}

	hasIndividualImageCut(imageDimension: SiImageDimension): boolean {
		return !this.baseImageCut || !this.baseImageCut.equals(imageDimension.imageCut);
	}
}
