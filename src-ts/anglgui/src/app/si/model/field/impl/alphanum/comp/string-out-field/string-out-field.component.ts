import { Component, OnInit, Input, ElementRef } from '@angular/core';
import { StringOutSiField } from "src/app/si/model/entity/impl/string/string-out-si-field";
import { StringFieldModel } from "src/app/ui/content/field/string-field-model";

@Component({
	selector: 'rocket-ui-string-out-field',
	templateUrl: './string-out-field.component.html',
	styleUrls: ['./string-out-field.component.css']
})
export class StringOutFieldComponent implements OnInit {

	model: StringFieldModel;
	
	constructor(elRef: ElementRef) { 
		elRef.nativeElement.classList.add('form-control-plaintext');
	}

	ngOnInit() {
	}

}
