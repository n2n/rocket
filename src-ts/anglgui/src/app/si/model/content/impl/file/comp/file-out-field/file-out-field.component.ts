import { AfterViewInit, Component, OnInit } from '@angular/core';
import { SiFile } from '../../model/file-in-si-field';
import { FileFieldModel } from '../file-field-model';
import GLightbox from 'glightbox';

@Component({
	selector: 'rocket-file-out-field',
	templateUrl: './file-out-field.component.html',
	styleUrls: ['./file-out-field.component.css'],
	host: {class: 'rocket-file-out-field'}
})
export class FileOutFieldComponent implements OnInit, AfterViewInit {

	model: FileFieldModel;
	
	constructor() { }
    ngAfterViewInit(): void {
		if (this.currentSiFile && this.currentSiFile.url) {
	        GLightbox({});
		}
    }
	
	get currentSiFile(): SiFile|null {
		return this.model.getSiFile();
	}

	ngOnInit() {
	}

}
