import { Component, OnInit } from '@angular/core';
import { InputInFieldModel } from '../input-in-field-model';

@Component({
	selector: 'rocket-input-in-field',
	templateUrl: './input-in-field.component.html'
})
export class InputInFieldComponent implements OnInit {
	model!: InputInFieldModel;

	constructor() { }

	ngOnInit(): void {
	}

	get grouped(): boolean {
		return this.model.getPrefixAddons().length > 0
				|| this.model.getSuffixAddons().length > 0;
	}

	get value(): string|null {
		return this.model.getValue();
	}

	set value(value: string|null) {
		if (value === '') {
			value = null;
		}
		this.model.setValue(value);
	}

	get autoerAvailable(): boolean {
		if (this.model.getAutoConfig) {
			return !!this.model.getAutoConfig();
		}

		return false;
	}

	set auto(auto: boolean) {
		if (this.model.setAuto) {
			this.model.setAuto(auto);
		}
	}

	get auto(): boolean {
		if (this.model.isAuto) {
			return this.model.isAuto();
		}

		return false;
	}
}
