import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { SiService } from 'src/app/si/manage/si.service';
import { SiFile } from '../../model/file-in-si-field';
import { FileInFieldModel } from '../file-in-field-model';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { PopupUiLayer } from 'src/app/ui/structure/model/ui-layer';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { ImageResizeComponent } from '../image-resize/image-resize.component';
import { SiCompFactory } from 'src/app/si/build/si-comp-factory';
import { ImageEditorComponent } from '../image-editor/image-editor.component';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { UploadResult, ImageEditorModel } from '../image-editor-model';


@Component({
	selector: 'rocket-file-in-field',
	templateUrl: './file-in-field.component.html',
	styleUrls: ['./file-in-field.component.css']
})
export class FileInFieldComponent implements OnInit {
	private uploader: CommonImageEditorModel;

	constructor(private siService: SiService, private translationService: TranslationService) { 
		this.uploader = new CommonImageEditorModel(this.siService, this.model.getSiFile())
	}

	get loading() {
		return !!this.uploader.uploadingFile || (this.currentSiFile && this.currentSiFile.thumbUrl && !this.imgLoaded);
	}

	get inputAvailable(): boolean {
		return !this.currentSiFile || (this.uploader.uploadInitiated && this.loading);
	}

	get currentSiFile(): SiFile|null {
		return this.model.getSiFile();
	}

	get removable(): boolean {
		if (this.loading) {
			return false;
		}

		if (this.currentSiFile) {
			return true;
		}

		if (this.fileInputRef && (this.fileInputRef.nativeElement as HTMLInputElement).value !== '') {
			return true;
		}

		return false;
	}

	get resizable(): boolean {
		return !this.loading && this.currentSiFile && this.currentSiFile.mimeType
				&& this.currentSiFile.imageDimensions.length > 0;
	}

	model: FileInFieldModel;
	uiStructure: UiStructure;
	imgLoaded = false;

	uploadResult: UploadResult|null = null;

	@ViewChild('fileInput', { static: false })
	fileInputRef: ElementRef;

	private popupUiLayer: PopupUiLayer|null = null;

	ngOnInit() {
		// const customUploader = (file: Blob): Promise<string> => {
		// 	this.reset();
		// 	this.fileInputRef.nativeElement.value = null;
		// 	return this.upload(file).then(siFile => siFile.url)
		// 		.catch((e) => { this.uploader.close(); throw e;  });
		// };

		// this.uploader = new Uppload({
  		// 	lang: en,
  		// 	uploader: customUploader
		// });
		// this.uploader.use([new Local(), new Camera(), new URL(), new Screenshot(), new Crop(), new Rotate(), new Blur(),
		// 		new Brightness(), new Flip(), new Contrast(), new Grayscale(), new HueRotate(), new Invert(),
		// 		new Saturate(), new Sepia() /*new Unsplash(), new Pixabay(), new Pexels()*/] as any[]);
	}

	getPrettySize(): string {
		let maxSize = this.model.getMaxSize();

		if (maxSize < 1024) {
			return maxSize.toLocaleString() + ' Bytes';
		}

		maxSize /= 1024;

		if (maxSize < 1024) {
			return maxSize.toLocaleString() + ' KB';
		}

		maxSize /= 1024;

		return maxSize.toLocaleString() + ' MB';
	}

	change(event: any) {
		this.reset();

		const fileList: FileList = event.target.files;

		if (fileList.length === 0) {
			return;
		}

		this.uploader.upload(fileList[0]).then((uploadErrorResult) => {
			this.uploadResult = uploadErrorResult;
		});
	}

	editImage() {
		if (this.popupUiLayer) {
			return;
		}

		const uiZone = this.uiStructure.getZone();

		this.popupUiLayer = uiZone.layer.container.createLayer();
		this.popupUiLayer.onDispose(() => {
			this.popupUiLayer = null;
		});

		this.popupUiLayer.pushZone(null).model = {
			title: 'Some Title',
			breadcrumbs: [],
			structureModel: new SimpleUiStructureModel(
					new TypeUiContent(ImageEditorComponent, (cr) => {
						cr.instance.model = this.uploader;
					}))
		};
	}

	resize() {
		if (this.popupUiLayer) {
			return;
		}

		const uiZone = this.uiStructure.getZone();

		this.popupUiLayer = uiZone.layer.container.createLayer();
		this.popupUiLayer.onDispose(() => {
			this.popupUiLayer = null;
		});

		this.popupUiLayer.pushZone(null).model = {
			title: 'Some Title',
			breadcrumbs: [],
			structureModel: new SimpleUiStructureModel(
					new TypeUiContent(ImageResizeComponent, (cr) => cr.instance.model = this.model))
		};
	}

	getAcceptStr(): string {
		const acceptParts = this.model.getAcceptedExtensions().map(ext => '.' + ext.split(',').join(''));
		acceptParts.push(...this.model.getAcceptedMimeTypes().map(ext => ext.split(',').join('')));

		return acceptParts.join(',');
	}

	private reset() {
		this.model.setSiFile(null);
		this.uploadResult = null;
	}

	removeCurrent() {
		this.reset();
		if (this.fileInputRef) {
			(this.fileInputRef.nativeElement as HTMLInputElement).value = '';
		}
	}
}

class CommonImageEditorModel implements ImageEditorModel {

	uploadInitiated = false;
	uploadingFile: Blob|null = null;

	constructor(private siService: SiService, private model: FileInFieldModel) {
	}

	getSiFile(): SiFile {
		return this.model.getSiFile();
	}

	upload(file: Blob): Promise<UploadResult> {
		if (file.size > this.model.getMaxSize()) {
			return Promise.resolve({ uploadTooLarge: true });
		}

		this.uploadingFile = file;
		this.uploadInitiated = true;

		return this.siService.fieldCall(this.model.getApiUrl(), this.model.getApiCallId(), {},
				new Map().set('upload', file)).toPromise().then((data) => {
					this.imgLoaded = false;
					this.uploadingFile = null;
					if (data.error) {
						return { uploadErrorMessage: data.error };
					}

					const siFile = SiCompFactory.buildSiFile(data.file);
					this.model.setSiFile(siFile);

					return { siFile };
				});
	}

	reset() {
		this.uploadingFile = null;
		this.uploadInitiated = false;
		this.uploadingFile = null;
	}


}
