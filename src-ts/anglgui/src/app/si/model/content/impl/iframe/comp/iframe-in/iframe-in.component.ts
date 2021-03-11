import {Component, OnInit, DoCheck} from '@angular/core';
import { IframeInModel } from '../iframe-in-model';

@Component({
	selector: 'rocket-iframe-in',
	templateUrl: './iframe-in.component.html'
})
export class IframeInComponent implements OnInit, DoCheck {
	model: IframeInModel;

	get formData() {
        return this.model.getFormData();
	}

	set formData(formData: Map<string, string>) {
        this.model.setFormData(formData);
	}

}
