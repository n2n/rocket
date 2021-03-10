import { Component, OnInit } from '@angular/core';

@Component({
	selector: 'rocket-date-time-in',
	templateUrl: './date-time-in.component.html'
})
export class DateTimeInComponent implements OnInit {
    date = new Date();

	constructor() { }

	ngOnInit(): void {
	}


}
