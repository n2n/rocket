import { Component, OnInit } from '@angular/core';
import { InputInFieldModel } from '../input-in-field-model';

@Component({
  selector: 'rocket-number-in',
  templateUrl: './number-in.component.html'
})
export class NumberInComponent implements OnInit {
	model: InputInFieldModel;
	constructor() { }
	ngOnInit(): void {
	}
	get grouped(): boolean {
		return true;
	}

	get value() {
		return this.model.getValue();
	}

	set value(value: string|null) {
		if (value === '') {
			value = null;
		}
		this.model.setValue(value);
	}

	nextStep(): void {
		if (null === this.model.getStep()) {
			return;
		}

		let step = this.model.getStep();

		this.value = (Math.floor(parseFloat(this.value) / this.model.getStep()) + this.model.getStep()).toString();
	}

	prevStep(): void {
		if (null === this.model.getStep()) {
			return;
		}
		this.value = (Math.floor(parseFloat(this.value) / this.model.getStep()) - this.model.getStep()).toString();
	}
}
