import { Component, OnInit, ViewChild, ElementRef, AfterViewInit, QueryList, ViewChildren } from '@angular/core';
import Cropper from 'cropperjs';
import { SiFile, SiImageDimension, SiImageCut } from '../../model/file-in-si-field';
import { ImageEditorModel } from '../image-editor-model';
import { ImagePreviewDirective } from './image-preview.directive';
import { Observable, Subject } from 'rxjs';

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

	imageSrc: ImageSrc;

	private ratioMap = new Map<string, ThumbRatio>();

	currentThumbRatio: ThumbRatio|null = null;
	currentImageDimension: SiImageDimension|null = null;

	constructor() { }

	ngOnInit() {
		this.imageSrc = new ImageSrc(this.canvasRef);

		this.initSiFile(this.model.getSiFile());
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

		this.imageSrc.init();

	}

	get thumbRatio() {
		return this.currentThumbRatio;
	}

	get originalActive(): boolean {
		return !this.currentThumbRatio;
	}

	get originalChanged(): boolean {
		return this.originalActive && this.imageSrc.changed;
	}

	private resetSelection() {
		this.currentThumbRatio = null;
		this.currentImageDimension = null;

		for (const [key, thumbRatio] of this.ratioMap) {
			thumbRatio.updateGroups();
		}
	}

	ensureOriginalUnchanged() {
		return this.originalChanged;
	}

	switchToOriginal() {
		this.resetSelection();
	}

	switchToThumbRatio(thumbRatio: ThumbRatio) {
		this.resetSelection();

		this.currentThumbRatio = thumbRatio;
		this.imageSrc.cut(thumbRatio.getGroupedImageCuts());
	}

	switchToImageDimension(thumbRatio: ThumbRatio, imageDimension: SiImageDimension) {
		this.resetSelection();

		this.currentImageDimension = imageDimension;
		this.imageSrc.cut([imageDimension.imageCut]);
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
		// this.cropper.getCroppedCanvas().toDataURL('image/jpeg');
	}

	save() {
		// this.cropper.getCroppedCanvas().toBlob();
	}
}

export class ImageSrc {
	private cropper: Cropper|null = null;

	changed = false;
	cropping = false;
	private cropBoxData: any;
	private imageCuts: SiImageCut[]|null = null;

	private readySubject: Subject<void>|null = new Subject<void>();

	constructor(private elemRef: ElementRef) {
	}

	init() {
		this.destroy();

		const readySubject = this.readySubject;

		this.cropper = new Cropper(this.elemRef.nativeElement, {
			viewMode: 1,
			preview: '.rocket-image-preview',
			zoomable: false,
			crop: (event) => {
				this.changed = true;

				if (!this.imageCuts) {
					return;
				}

				const data = this.cropper.getData();
				for (const imageCut of this.imageCuts) {
					imageCut.x = data.x;
					imageCut.y = data.y;
					imageCut.width = data.width;
					imageCut.height = data.height;
				}

				// console.log(event.type);
				// console.log(event.detail.x);
				// console.log(event.detail.y);
				// console.log(event.detail.width);
				// console.log(event.detail.height);
				// console.log(event.detail.rotate);
				// console.log(event.detail.scaleX);
				// console.log(event.detail.scaleY);
			},
			ready: () => {
				if (readySubject === this.readySubject) {
					this.readySubject = null;
					readySubject.next();
					readySubject.complete();
				}
				// console.log(this.cropper.getCanvasData());
				// console.log(this.cropper.getContainerData());

				// if (imageCut) {
				// 	this.cropper.setCropBoxData({ left: imageCut.x, top: imageCut.y, width: imageCut.width,
				// 			height: imageCut.height });
				// // 		rotate: 0, scaleX: 1, scaleY: 1 });
				// } else {
				// 	// this.cropper.setCropBoxData(this.cropper.getCanvasData());
				// 	this.cropper.clear();
				// }
			}
		});
	}

	private calcRatio(): number {
		const imageData = this.cropper.getImageData();

		return imageData.width / imageData.naturalWidth;
	}

	cut(imageCuts: SiImageCut[]|null) {
		this.changed = false;
		this.imageCuts = imageCuts;

		if (!imageCuts) {
			this.cropper.clear();
			return;
		}

		for (const imageCut of imageCuts) {
			this.cropper.setData({
				x: imageCut.x, y: imageCut.y, width: imageCut.width, height: imageCut.height
			});
		}
		this.changed = false;
	}

	destroy() {
		if (!this.cropper) {
			return;
		}

		this.changed = false;

		this.cropper.destroy();
		this.cropper = null;

		if (this.readySubject) {
			this.readySubject.complete();
		}

		this.readySubject = new Subject<void>();
	}

	rotateCw() {
		this.changed = true;
		this.cropper.rotate(90);
	}

	rotateCcw() {
		this.changed = true;
		this.cropper.rotate(-90);
	}

	toggleCropping() {
		this.cropping = !this.cropping;

		if (this.cropping) {
			this.changed = true;
			this.cropper.crop();
		} else {
			this.cropper.clear();
		}
	}

	get ready(): boolean {
		return !this.readySubject;
	}

	get ready$(): Observable<void> {
		if (this.readySubject) {
			return this.readySubject;
		}

		return new Observable<void>(subscriber => {
			subscriber.next();
			subscriber.complete();
		});
	}

	// createPreviewDataUrl(imageCut: SiImageCut): string {
	// 	if (!this.ready) {
	// 		throw new Error('Cropper not yet ready.');
	// 	}

	// 	if (this.cropping) {
	// 		this.cropBoxData = this.cropper.getCropBoxData();
	// 	}

	// 	this.cut(new SiImageCut(100, 100, 50, 50, false));
	// 	const dataUrl = this.cropper.getCroppedCanvas().toDataURL();

	// 	if (this.cropBoxData) {
	// 		this.cropper.setCropBoxData(this.cropBoxData);
	// 		this.cropBoxData = null;
	// 	} else {
	// 		this.cropper.clear();
	// 	}

	// 	return dataUrl;
	// }

}

export class ThumbRatio {
	public imageDimensions = new Array<SiImageDimension>();
	// private _largestImageDimension: SiImageDimension;

	private groupedImageCuts: SiImageCut[];
	private imgCutDimMap = new Map<string, SiImageDimension[]>();

	private groupedPreviewElementRef: ElementRef|null = null;
	private imgDimPreviewElements = new Map<string, ElementRef>();

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

	registerPreviewImg(elementRef: ElementRef, imageDimension: SiImageDimension|null) {
		if (!imageDimension) {
			this.groupedPreviewElementRef = elementRef;
		} else {
			this.imgDimPreviewElements.set(imageDimension.id, elementRef);
		}
	}

	unregisterPreviewImg(elementRef: ElementRef) {
		if (this.groupedPreviewElementRef === elementRef) {
			this.groupedPreviewElementRef = null;
			return;
		}

		for (const [key, elemRef] of this.imgDimPreviewElements) {
			if (elemRef === elementRef) {
				this.imgDimPreviewElements.delete(key);
				return;
			}
		}

		throw new Error('Unkown preview ' + elementRef);
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

		this.determineGroupedImageCut();
	}

	updateGroups() {
		this.imgCutDimMap.clear();

		for (const imageDimension of this.imageDimensions) {
			this.classifyImageDimension(imageDimension);
		}

		this.determineGroupedImageCut();
	}

	private classifyImageDimension(imageDimension: SiImageDimension) {
		const key = this.imgCutKey(imageDimension.imageCut);
		if (!this.imgCutDimMap.has(key)) {
			this.imgCutDimMap.set(key, []);
		}

		this.imgCutDimMap.get(key).push(imageDimension);
	}

	private determineGroupedImageCut() {
		this.groupedImageCuts = null;

		let lastSize = 0;
		for (const [key, imgDims] of this.imgCutDimMap) {
			if (lastSize >= imgDims.length || imgDims.length <= 1) {
				continue;
			}

			this.groupedImageCuts = imgDims.map(imgDim => imgDim.imageCut);
			lastSize = imgDims.length;
		}
	}

	private imgCutKey(imgCut: SiImageCut): string {
		return imgCut.width + ',' + imgCut.height + ',' + imgCut.x + ',' + imgCut.y;
	}

	hasGroupedImageCuts(): boolean {
		return !!this.groupedImageCuts;
	}

	hasIndividualImageCut(imageDimension: SiImageDimension): boolean {
		return !this.groupedImageCuts || !this.groupedImageCuts[0].equals(imageDimension.imageCut);
	}

	getGroupedImageCuts(): SiImageCut[] {
		if (this.groupedImageCuts) {
			return this.groupedImageCuts;
		}

		throw new Error('No base image cut available.');
	}

	getGroupedPreviewImageCut(currentImageDimension: SiImageDimension|null): SiImageCut {
		for (const imgDim of this.getGroupedImageCuts()) {
			if (!currentImageDimension || imgDim !== currentImageDimension.imageCut) {
				return imgDim;
			}
		}

		throw new Error();
	}
}
