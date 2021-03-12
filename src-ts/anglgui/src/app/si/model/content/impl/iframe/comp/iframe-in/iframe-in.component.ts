import {Component, OnInit, DoCheck} from '@angular/core';
import { IframeInModel } from '../iframe-in-model';

@Component({
selector: 'rocket-iframe-in',
templateUrl: './iframe-in.component.html'
})
export class IframeInComponent implements OnInit, DoCheck {
	model: IframeInModel;

	ngOnInit(): void {
		// console.log('new ifc');
		// console.log(this.formData);
	}

	ngDoCheck(): void {
		// console.log(this.formData);
	}

	get formData(): Map<string, string> {
		return this.model.getFormData();
	}

	set formData(formData: Map<string, string>) {
		this.model.setFormData(formData);
	}

}
