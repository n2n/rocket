import { Subject, Observable } from 'rxjs';
import { SiImageCut } from '../../model/file-in-si-field';
import { ElementRef } from '@angular/core';
import Cropper from 'cropperjs';

export class ImageSrc {

	private cropper: Cropper|null = null;

	changed = false;
	cropping = false;
	private cropBoxData: any;
	private imageCuts: SiImageCut[]|null = null;

	private readySubject: Subject<void>|null = new Subject<void>();

	constructor(private elemRef: ElementRef, private mimeType: string) {
	}

	init() {
		this.destroy();

		this.cropper = new Cropper(this.elemRef.nativeElement, {
			viewMode: 1,
			preview: '.rocket-image-preview',
			zoomable: false,
			crop: (event) => {
				this.changed = true;
				this.cropping = true;

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
				if (!this.readySubject) {
					throw new Error('No ready subject.');
				}

				const readySubject = this.readySubject;
				this.readySubject = null;
				readySubject.next();
				readySubject.complete();
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

	replace(url: string) {
		this.readySubject = new Subject<void>();
		this.cropper.replace(url);
	}

	private calcRatio(): number {
		const imageData = this.cropper.getImageData();

		return imageData.width / imageData.naturalWidth;
	}

	cut(imageCuts: SiImageCut[]|null) {
		this.imageCuts = imageCuts;

		if (!imageCuts) {
			this.cropper.clear();
			this.cropping = false;
			this.changed = false;
			return;
		}

		const imageCut = imageCuts[0];

		if (!imageCut) {
			throw new Error('Empty ImageCut Array.');
		}

		const cropData = {
			x: imageCut.x, y: imageCut.y, width: imageCut.width, height: imageCut.height
		};

		this.cropper.crop();
		this.cropper.setData(cropData);
		this.changed = false;
	}

	// get freeRatioAllowed(): boolean {
	// 	return this.imageCuts || this.imageCuts[0].
	// }

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

	createBlob(): Promise<Blob> {
		return new Promise((resolve) => {
			this.cropper.getCroppedCanvas().toBlob((blob) => {
				resolve(blob);
			}, this.mimeType);
		});
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
