import { Component, OnInit, Input } from '@angular/core';
import { BehaviorSubject } from "rxjs";
import { InputInFieldModel } from "src/app/ui/content/field/input-in-field-model";

@Component({
  selector: 'rocket-input-in-field',
  templateUrl: './input-in-field.component.html'
})
export class InputInFieldComponent implements OnInit {
	model: InputInFieldModel;
	
	constructor() { }
	
	ngOnInit() {
	}

	get value() {
		return this.model.getValue();
	}
	
	set value(value: string|null) {
		if (value == '') {
			value = null;
		}
		this.model.setValue(value);
	}
}
