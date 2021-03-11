import { NgbDateStruct,  NgbDateAdapter } from '@ng-bootstrap/ng-bootstrap';
import { Injectable } from '@angular/core';

@Injectable()
export class UiDateAdapter extends NgbDateAdapter<Date> {


	fromModel(date: Date): NgbDateStruct {
		if (!date) {
			return null;
		}

		return {
			year: date.getFullYear(),
			month: date.getMonth(),
			day: date.getDate()
		};
	}

  	toModel(dateStruct: NgbDateStruct): Date {
		if (!dateStruct) {
			return null;
		}
		return new Date(dateStruct.year, dateStruct.month, dateStruct.day);
	}
}
