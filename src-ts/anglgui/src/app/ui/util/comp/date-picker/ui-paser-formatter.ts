import { NgbDateStruct, NgbDateParserFormatter } from '@ng-bootstrap/ng-bootstrap';
import { LOCALE_ID } from '@angular/core';

export class UiParserFormatter extends NgbDateParserFormatter {
	constructor() {
		super();
	}

	parse(value: string): NgbDateStruct {
		const date = new Date(value);

		return {
			year: date.getFullYear(),
			month: date.getMonth(),
			day: date.getSeconds()
		};
	}

	format(date: NgbDateStruct): string {
        console.log(LOCALE_ID);
		return new Date(date.year, date.month, date.year).toLocaleDateString('de-CH');
	}
}
