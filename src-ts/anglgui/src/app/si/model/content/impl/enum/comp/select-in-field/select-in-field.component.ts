import { Component, OnInit } from '@angular/core';
import { SelectInFieldModel } from '../select-in-field-model';
import { Option } from 'src/app/ui/util/comp/select-input/select.component';

@Component({
	selector: 'rocket-select-in-field',
	templateUrl: './select-in-field.component.html',
	styleUrls: ['./select-in-field.component.css']
})
export class SelectInFieldComponent implements OnInit {

	model: SelectInFieldModel;

	constructor() { }

	ngOnInit() {
	}

	get optional() {
		return !this.model.isMandatory();
	}

	get value(): string|null {
		return this.model.getValue();
	}

	get options(): Option[] {
		const options: Option[] = [];

		for (const [value, label] of this.model.getOptions()) {
			options.push({ value, label });
		}

		return options;
	}
}
