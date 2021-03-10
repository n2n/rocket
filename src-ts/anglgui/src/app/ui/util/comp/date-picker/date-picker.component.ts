import { Component, OnInit, EventEmitter, Output, Input } from '@angular/core';
import { NgbDateParserFormatter, NgbDateAdapter } from '@ng-bootstrap/ng-bootstrap';
import { UiParserFormatter } from './ui-paser-formatter';
import { UiDateAdapter } from './ui-date-adapter';

@Component({
	selector: 'rocket-ui-date-picker',
	templateUrl: './date-picker.component.html',
	providers: [
		{ provide: NgbDateParserFormatter, useClass: UiParserFormatter },
		{ provide: NgbDateAdapter, useClass: UiDateAdapter }
	]
})
export class DatePickerComponent implements OnInit {


	private pDate: Date|null = null;

	@Input()
	set date(date: Date|null) {
		this.pDate = date;
	}

	get date(): Date {
		return this.pDate;
	}

	@Output()
	private dateChange = new EventEmitter<Date|null>();
	// dateStruct: NgbDateStruct|null = null;

	// constructor(@Inject(LOCALE_ID) private localeId: string) { }

	ngOnInit(): void {
		// this.dateStruct =  {
		// 	year: this.date.getFullYear(),
		// 	month: this.date.getMonth(),
		// 	day: this.date.getDate()
		// };
	}

	selectDate(date: Date|null) {
		if (!this.pDate || !date) {
			this.pDate = date;
			this.dateChange.emit(this.pDate);
			return;
		}

		this.pDate.setDate(date.getDate());
		this.pDate.setMonth(date.getMonth());
		this.pDate.setFullYear(date.getFullYear());
		this.dateChange.emit(this.pDate);
	}
}


