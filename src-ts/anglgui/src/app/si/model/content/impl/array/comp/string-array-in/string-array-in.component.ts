import { Component, OnInit } from '@angular/core';
import { StringArrayInModel } from '../string-array-in-model';

@Component({
  selector: 'rocket-string-array-in',
  templateUrl: './string-array-in.component.html'
})
export class StringArrayInComponent implements OnInit {

  	model: StringArrayInModel;

	constructor() { }

	ngOnInit(): void {
	}

	get values(): string[] {
		return this.model.getValues();
	}

	set values(values: string[]) {
		this.model.setValues(values);
	}

	get disabled(): boolean {
		return this.values.length >= this.model.getMax();
	}

	addElement(): void {

	}
}
