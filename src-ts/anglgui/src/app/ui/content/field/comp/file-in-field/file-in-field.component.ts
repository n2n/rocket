import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { SiFile } from 'src/app/si/model/content/impl/file-in-si-field';
import { BehaviorSubject } from 'rxjs';
import { SiService } from 'src/app/si/model/si.service';
import { FileInFieldModel } from '../../file-in-field-model';
import { SiResultFactory } from 'src/app/si/build/si-result-factory';

@Component({
  selector: 'rocket-file-in-field',
  templateUrl: './file-in-field.component.html',
  styleUrls: ['./file-in-field.component.css']
})
export class FileInFieldComponent implements OnInit {
	imgLoaded = false;
	mandatory = true;
	model: FileInFieldModel;
	mimeTypes: string[] = [];

	uploadInitiated = false;
	uploadingFile: File|null = null;
	uploadTooLarge = false;
	uploadErrorMessage: string|null = null;

	@ViewChild('fileInput', { static: false })
	fileInputRef: ElementRef;


	constructor(private siService: SiService) { }

	ngOnInit() {
	}

	get loading() {
		return !!this.uploadingFile || (this.currentSiFile && this.currentSiFile.thumbUrl && !this.imgLoaded);
	}

	getPrettySize(): string {
		return (this.model.getMaxSize() / 1024 / 1024).toLocaleString() + 'MB';
	}

	get inputAvailable(): boolean {
		return !this.currentSiFile || (this.uploadInitiated && this.loading)
	}

	get currentSiFile(): SiFile|null {
		return this.model.getSiFile();
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
