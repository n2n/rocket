import { Component, OnInit, ViewChild, ElementRef, AfterViewInit } from '@angular/core';
import { SiFile, SiImageDimension } from '../../model/file-in-si-field';
import { ImageEditorModel, UploadResult } from '../image-editor-model';
import { ImageSrc } from './image-src';
import { ThumbRatio } from './thumb-ratio';

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

	uploadResult: UploadResult;
	private saving = false;

	constructor() { }

	ngOnInit() {
		this.imageSrc = new ImageSrc(this.canvasRef, this.model.getSiFile().mimeType);

		this.imageSrc.ready$.subscribe(() => {
			this.imageSrc.cut(null, null);
		});

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
		return !this.currentThumbRatio && !this.currentImageDimension;
	}

	get originalChanged(): boolean {
		return this.originalActive && this.imageSrc.changed;
	}

	get freeRatioOption(): boolean {
		return !this.originalActive && this.imageSrc.freeRatioAllowed;
	}

	saveOriginal() {
		if (this.loading) {
			return;
		}

		this.uploadResult = null;
		this.saving = true;
		this.imageSrc.createBlob().then((blob) => {
			this.model.upload(blob, this.model.getSiFile().name).then((uploadResult) => {
				this.saving = false;
				this.handleUploadResult(uploadResult);
			});
		});
	}

	private handleUploadResult(uploadResult: UploadResult) {
		this.uploadResult = uploadResult;

		if (!uploadResult.siFile) {
			return;
		}

		const siFile = uploadResult.siFile;
		this.model.setSiFile(siFile);
		this.imageSrc.replace(siFile.url);
		this.initSiFile(siFile);

		this.imageSrc.ready$.subscribe(() => {
			this.imageSrc.cut(null, null);
		});
	}

	private resetSelection() {
		this.currentThumbRatio = null;
		this.currentImageDimension = null;

		for (const [, thumbRatio] of this.ratioMap) {
			thumbRatio.updateGroups();
		}
	}

	get loading(): boolean {
		return !this.imageSrc.ready || this.saving;
	}

	ensureOriginalUnchanged() {
		return this.originalChanged;
	}

	switchToOriginal() {
		this.resetSelection();

		this.imageSrc.cut(null, null);
	}

	switchToThumbRatio(thumbRatio: ThumbRatio) {
		this.resetSelection();

		this.currentThumbRatio = thumbRatio;
		this.imageSrc.cut(thumbRatio.getGroupedImageCuts(), {
			ratio: thumbRatio.imageDimensions[0].width / thumbRatio.imageDimensions[0].height,
			freeRatioAllowed: !thumbRatio.ratioFixed
		});
	}

	switchToImageDimension(imageDimension: SiImageDimension) {
		this.resetSelection();

		this.currentImageDimension = imageDimension;
		this.imageSrc.cut([imageDimension.imageCut], {
			ratio: imageDimension.width / imageDimension.height,
			freeRatioAllowed: !imageDimension.ratioFixed
		});
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

	isLowRes(imageDimension: SiImageDimension): boolean {
		return imageDimension.width > imageDimension.imageCut.width
				|| imageDimension.height > imageDimension.imageCut.height;
	}
}