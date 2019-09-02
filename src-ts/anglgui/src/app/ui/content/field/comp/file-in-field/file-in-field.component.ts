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

	uploadingFile: File|null = null;

	constructor(private siService: SiService) { }

	ngOnInit() {
	}

	change(event: any) {
		const fileList: FileList = event.target.files;

		if (fileList.length === 0) {
			this.uploadingFile = null;
			return;
		}

		const file = this.uploadingFile = fileList[0];

		this.siService.fieldCall(this.model.getApiUrl(), this.model.getApiCallId(),	{}, new Map().set('upload', file))
				.subscribe((data) => {
					this.model.setSiFile(SiResultFactory.buildSiFile(data.file));
				});
	}

}
