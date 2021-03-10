import { Component, OnInit } from '@angular/core';
import { NgbDateParserFormatter } from '@ng-bootstrap/ng-bootstrap';
import { UiParserFormatter } from './ui-paser-formatter';

@Component({
	selector: 'rocket-date-picker',
	templateUrl: './date-picker.component.html',
	providers: [ { provide: NgbDateParserFormatter, useClass: UiParserFormatter}]
})
export class DatePickerComponent implements OnInit {

	date: Date;

	constructor() { }

	ngOnInit(): void {
	}

}


