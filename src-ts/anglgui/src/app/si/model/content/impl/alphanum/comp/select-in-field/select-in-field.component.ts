import { Component, OnInit } from '@angular/core';
import { SelectInFieldModel } from '../../select-in-field-model';

@Component({
	selector: 'rocket-select-in-field',
	templateUrl: './select-in-field.component.html',
	styleUrls: ['./select-in-field.component.css']
})
export class SelectInFieldComponent implements OnInit {

	model: SelectInFieldModel;

	constructor() { }

	ngOnInit() {
	}

}
