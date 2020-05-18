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
import {
	Uppload, Local, xhrUploader, en, Camera, URL, Screenshot, Crop, Rotate,
	Blur, Brightness, Flip, Contrast, Grayscale, HueRotate, Invert, Saturate, Sepia
} from 'uppload';
import { SiFieldFactory } from 'src/app/si/build/si-field-factory';


@Component({
	selector: 'rocket-file-in-field',
	templateUrl: './file-in-field.component.html',
	styleUrls: ['./file-in-field.component.css']
})
export class FileInFieldComponent implements OnInit {

	constructor(private siService: SiService) { }

	get loading() {
		return !!this.uploadingFile || (this.currentSiFile && this.currentSiFile.thumbUrl && !this.imgLoaded);
	}

	get inputAvailable(): boolean {
		return !this.currentSiFile || (this.uploadInitiated && this.loading);
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
		return !this.loading && this.currentSiFile && this.currentSiFile.imageDimensions.length > 0;
	}

	model: FileInFieldModel;
	uiStructure: UiStructure;
	imgLoaded = false;

	uploadInitiated = false;
	uploadingFile: File|null = null;
	uploadTooLarge = false;
	uploadErrorMessage: string|null = null;

	@ViewChild('fileInput', { static: false })
	fileInputRef: ElementRef;

	private popupUiLayer: PopupUiLayer|null = null;

	ngOnInit() {
		const customUploader = (file: Blob): Promise<string> => {
			return this.upload(file).then(siFile => siFile.url);
		};

		const uploader = new Uppload({
  			lang: en,
  			uploader: customUploader
		});
		uploader.use([new Local(), new Camera(), new URL(), new Screenshot(), new Crop(), new Rotate(), new Blur(),
				new Brightness(), new Flip(), new Contrast(), new Grayscale(), new HueRotate(), new Invert(),
				new Saturate(), new Sepia() /*new Unsplash(), new Pixabay(), new Pexels()*/] as any[]);

		uploader.open();
	}

	getPrettySize(): string {
		return (this.model.getMaxSize() / 1024 / 1024).toLocaleString() + 'MB';
	}

	change(event: any) {
		this.reset();

		const fileList: FileList = event.target.files;

		if (fileList.length === 0) {
			this.uploadingFile = null;
			return;
		}

		const file = fileList[0];
	}

	private upload(file: Blob): Promise<SiFile> {
		if (file.size > this.model.getMaxSize()) {
			this.uploadTooLarge = true;
			return Promise.reject('too large');
		}

		return this.siService.fieldCall(this.model.getApiUrl(), this.model.getApiCallId(), {}, new Map().set('upload', file))
				.toPromise().then((data) => {
					this.imgLoaded = false;
					this.uploadingFile = null;
					if (data.error) {
						throw new Error(data.error);
					}

					const siFile = SiCompFactory.buildSiFile(data.file);
					this.model.setSiFile(siFile);

					return siFile;
				});
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
		this.uploadInitiated = false;
		this.uploadTooLarge = false;
		this.uploadErrorMessage = null;
		this.uploadingFile = null;
	}

	removeCurrent() {
		this.reset();
		if (this.fileInputRef) {
			(this.fileInputRef.nativeElement as HTMLInputElement).value = '';
		}
	}
}
