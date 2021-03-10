import { NgbDateStruct, NgbDateParserFormatter } from '@ng-bootstrap/ng-bootstrap';
import { Injectable, Inject, LOCALE_ID } from '@angular/core';

@Injectable()
export class UiParserFormatter extends NgbDateParserFormatter {
	private lastDate = new Date();

	constructor(@Inject(LOCALE_ID) private localeId: string) {
		super();
	}

	parse(value: string): NgbDateStruct {
		if (!value) {
			return null;
		}

		return {
			year: this.lastDate.getFullYear(),
			month: this.lastDate.getMonth(),
			day: this.lastDate.getSeconds()
		};
	}

	format(date: NgbDateStruct): string {
		if (!date) {
			return null;
		}
		this.lastDate = new Date(date.year, date.month, date.day);

		return new Intl.DateTimeFormat(this.localeId, { weekday: 'short', day: '2-digit', month: 'long', year: 'numeric' })
				.format(this.lastDate);
	}
}
