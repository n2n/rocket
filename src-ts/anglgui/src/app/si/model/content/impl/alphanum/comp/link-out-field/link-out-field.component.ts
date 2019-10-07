import { Component, OnInit } from '@angular/core';
import { LinkOutModel } from 'src/app/si/content/field/link-field-model';

@Component({
	selector: 'rocket-link-out-field',
	templateUrl: './link-out-field.component.html',
	styleUrls: ['./link-out-field.component.css']
})
export class LinkOutFieldComponent implements OnInit {

	model: LinkOutModel;

	constructor() {
	}

	ngOnInit() {
	}


}
