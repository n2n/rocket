import { Component, OnInit, Input } from '@angular/core';
import { BehaviorSubject } from "rxjs";

@Component({
  selector: 'rocket-input-in-field',
  templateUrl: './input-in-field.component.html'
})
export class InputInFieldComponent implements OnInit {

	mandatory = false;
	maxlength: number|null = null; 
	readonly value$ = new BehaviorSubject<string|null>(null);
	
	constructor() { }
	
	ngOnInit() {
	}
	
	ngOnDestroy() {
		this.value$.complete();
	}

	get value() {
		return this.value$.getValue();
	}
	
	set value(value: string|null) {
		this.value$.next(value);
	}
}
