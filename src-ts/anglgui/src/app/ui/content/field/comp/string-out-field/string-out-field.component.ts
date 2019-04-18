import { Component, OnInit, Input, ElementRef } from '@angular/core';
import { StringOutSiField } from "src/app/si/model/content/impl/string-out-si-field";

@Component({
  selector: 'rocket-ui-string-out-field',
  templateUrl: './string-out-field.component.html',
  styleUrls: ['./string-out-field.component.css']
})
export class StringOutFieldComponent implements OnInit {

	@Input() value: string|null;
	
	constructor(elRef: ElementRef) { 
		elRef.nativeElement.classList.add('form-control-plaintext');
	}

	ngOnInit() {
	}

}
