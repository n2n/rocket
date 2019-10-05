import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { SiFile } from 'src/app/si/model/entity/impl/file/file-in-si-field';
import { SiService } from 'src/app/si/model/si.service';
import { FileInFieldModel } from '../../file-in-field-model';
import { SiResultFactory } from 'src/app/si/build/si-result-factory';
import { PopupUiLayer } from 'src/app/si/model/structure/ui-layer';
import { SimpleSiStructureModel } from 'src/app/si/model/structure/impl/simple-ui-structure-model';
import { ImageResizeComponent } from '../../../file/image-resize/image-resize.component';
import { TypeSiContent } from 'src/app/si/model/structure/impl/type-si-content';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';

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
	siStructure: UiStructure;
	imgLoaded = false;

	uploadInitiated = false;
	uploadingFile: File|null = null;
	uploadTooLarge = false;
	uploadErrorMessage: string|null = null;

	@ViewChild('fileInput', { static: false })
	fileInputRef: ElementRef;

	private popupUiLayer: PopupUiLayer|null = null;

	ngOnInit() {
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

		if (file.size > this.model.getMaxSize()) {
			this.uploadTooLarge = true;
			return;
		}

		this.uploadingFile = file;
		this.uploadInitiated = true;

		this.siService.fieldCall(this.model.getApiUrl(), this.model.getApiCallId(),	{}, new Map().set('upload', file))
				.subscribe((data) => {
					if (file !== this.uploadingFile) {
						return;
					}

					this.imgLoaded = false;
					this.uploadingFile = null;

					if (data.error) {
						this.uploadErrorMessage = data.error;
					} else {
						this.model.setSiFile(SiResultFactory.buildSiFile(data.file));
					}
				});
	}

	resize() {
		if (this.popupUiLayer) {
			return;
		}

		const uiZone = this.siStructure.getZone();

		this.popupUiLayer = uiZone.layer.container.createLayer();
		this.popupUiLayer.onDispose(() => {
			this.popupUiLayer = null;
		});

		this.popupUiLayer.pushZone(null).model = {
			title: 'Some Title',
			breadcrumbs: [],
			structureModel: new SimpleSiStructureModel(
					new TypeSiContent(ImageResizeComponent, (cr) => cr.instance.model = this.model)),
			controls: []
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
