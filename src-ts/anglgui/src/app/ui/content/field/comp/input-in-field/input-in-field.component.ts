import { Component, OnInit, Input } from '@angular/core';
import { BehaviorSubject } from "rxjs";
import { StringInFieldModel } from "src/app/ui/content/field/string-in-field-model";

@Component({
  selector: 'rocket-input-in-field',
  templateUrl: './input-in-field.component.html'
})
export class InputInFieldComponent implements OnInit {
	model: StringInFieldModel;
	
	constructor() { }
	
	ngOnInit() {
	}
	
	

	get value() {
		return this.model.getValue();
	}
	
	set value(value: string|null) {
		this.model.setValue(value);
	}
}
