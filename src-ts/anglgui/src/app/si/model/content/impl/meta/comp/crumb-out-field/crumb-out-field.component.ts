import { Component, OnInit, HostBinding } from '@angular/core';
import { CrumbFieldModel } from '../../model/crumb-field-model';

@Component({
	selector: 'rocket-crumb-out-field',
	templateUrl: './crumb-out-field.component.html',
	styleUrls: ['./crumb-out-field.component.css'],
	host: {class: 'rocket-crumb-out-field d-flex'}
})
export class CrumbOutFieldComponent implements OnInit {
	model: CrumbFieldModel;

	// @HostBinding('class.rocket-content-compact')
	// compact = false;

	constructor() { }

	ngOnInit() {
	}



}