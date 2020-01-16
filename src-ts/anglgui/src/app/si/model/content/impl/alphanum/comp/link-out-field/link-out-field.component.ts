import { Component, OnInit } from '@angular/core';
import { LinkOutModel } from '../link-field-model';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';

@Component({
	selector: 'rocket-link-out-field',
	templateUrl: './link-out-field.component.html',
	styleUrls: ['./link-out-field.component.css']
})
export class LinkOutFieldComponent implements OnInit {

	uiZone: UiZone;
	model: LinkOutModel;

	constructor() {
	}

	ngOnInit() {
	}
}
