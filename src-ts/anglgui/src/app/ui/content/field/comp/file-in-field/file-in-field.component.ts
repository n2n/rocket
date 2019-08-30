import { Component, OnInit } from '@angular/core';
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

	mandatory = true;
	model: FileInFieldModel;
	mimeTypes: string[] = [];

	constructor(private siService: SiService) { }

	ngOnInit() {
	}

	get currentSiFile(): SiFile|null {
		return this.currentSiFile$.getValue();
	}

	set currentSiFile(siFile: SiFile|null) {
		this.currentSiFile$.next(siFile);
	}

	get uploadedFile(): File|null {
		return this.uploadedFile$.getValue();
	}

	set uploadedFile(uploadedFile: File|null) {
		this.uploadedFile$.next(uploadedFile);
	}

	change(event: any) {
		const fileList: FileList = event.target.files;

		if (fileList.length === 0) {
			this.uploadedFile = null;
			return;
		}

		this.siService.fieldCall(this.model.getApiUrl(), this.model.getApiCallId(),
						new Map().set('uploadedFile', fileList[0]))
				.subscribe((data) => {
					this.model.setSiFile(SiResultFactory.buildSiFile(data.file));
				});
	}

}
