import { Component, OnInit, LOCALE_ID } from '@angular/core';
import { formatNumber } from '@angular/common';

@Component({
	selector: 'rocket-date-time-in',
	templateUrl: './date-time-in.component.html'
})
export class DateTimeInComponent implements OnInit {
    date = new Date();

    localeId = LOCALE_ID.toString();

	constructor() { }

	ngOnInit(): void {

	}

    num = 1.2;
    numStr = '1.2';
}
