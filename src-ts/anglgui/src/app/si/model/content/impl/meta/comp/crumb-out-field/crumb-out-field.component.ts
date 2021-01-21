import { Component, OnInit } from '@angular/core';
import { CrumbFieldModel } from '../../model/crumb-field-model';

@Component({
	selector: 'rocket-crumb-out-field',
	templateUrl: './crumb-out-field.component.html',
	styleUrls: ['./crumb-out-field.component.css'],
	host: {class: 'rocket-crumb-out-field'}
})
export class CrumbOutFieldComponent implements OnInit {

	model: CrumbFieldModel;

	constructor() { }

	ngOnInit() {
	}

}
