import { Component, OnInit, Input, EventEmitter, Output } from '@angular/core';
import { NgbTimeStruct } from '@ng-bootstrap/ng-bootstrap';

@Component({
	selector: 'rocket-ui-time-picker',
	templateUrl: './time-picker.component.html'
})
export class TimePickerComponent implements OnInit {

	@Output()
	private dateChange = new EventEmitter<Date>();

	private pDate = new Date();
	private pTimeStruct: NgbTimeStruct;

	ngOnInit(): void {
		this.date = this.pDate;
	}

	@Input()
	set date(date: Date) {
		this.pDate = date;
		this.pTimeStruct = {
			hour: date.getHours(),
			minute: date.getMinutes(),
			second: date.getSeconds()
		};
	}


	get timeStruct(): NgbTimeStruct {
		return this.pTimeStruct;
	}

	set timeStruct(timeStruct: NgbTimeStruct) {
		this.pDate.setHours(timeStruct.hour);
		this.pDate.setMinutes(timeStruct.minute);
		this.pDate.setSeconds(timeStruct.second);
		this.dateChange.emit(this.pDate);
	}
}
