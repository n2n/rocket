import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'rocket-input-in-field',
  templateUrl: './input-in-field.component.html',
  styleUrls: ['./input-in-field.component.css']
})
export class InputInFieldComponent implements OnInit {

	mandatory = false;
	maxlength: number|null = null; 
	value: string|null;
	
	constructor() { }

	ngOnInit() {
	}

	
}
