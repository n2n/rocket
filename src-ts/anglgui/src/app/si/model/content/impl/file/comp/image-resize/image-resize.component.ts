import { Component, OnInit } from '@angular/core';
import { FileInFieldModel } from '../file-in-field-model';

@Component({
	selector: 'rocket-image-resize',
	templateUrl: './image-resize.component.html',
	styleUrls: ['./image-resize.component.css']
})
export class ImageResizeComponent implements OnInit {

	model: FileInFieldModel;

	constructor() { }

	ngOnInit() {
	}

}
