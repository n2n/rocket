import { Component, OnInit } from '@angular/core';
import { EnumInModel } from '../enum-in-model';

@Component({
	selector: 'rocket-enum-in',
	templateUrl: './enum-in.component.html',
	styleUrls: ['./enum-in.component.css']
})
export class EnumInComponent implements OnInit {
	model: EnumInModel;

	constructor() { }

	ngOnInit() {
	}

	get value(): string {
		return this.model.getValue();
	}

	set value(value: string) {
		this.model.setValue(value);
	}

	get options(): Map<string, string> {
		return this.model.getOptions();
	}

}
