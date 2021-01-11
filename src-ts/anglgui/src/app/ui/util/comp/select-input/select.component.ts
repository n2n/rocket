import { Component, OnInit, ViewChild, ElementRef, Input, Output, EventEmitter } from '@angular/core';
import { Subscription, fromEvent, merge } from 'rxjs';
import { filter, takeUntil } from 'rxjs/operators';

@Component({
	selector: 'rocket-ui-select',
	templateUrl: './select.component.html',
	styleUrls: ['./select.component.css']
})
export class SelectComponent implements OnInit {

	private _value: string|null = null;
	private popupSubscription: Subscription|null = null;

	@ViewChild('dropdown', { static: true })
	private dropdownElemRef: ElementRef;

	@Output()
	valueChange = new EventEmitter<string|null>();

	@Input()
	options = new Array<Option>();
	@Input()
	optional = true;
	@Input()
	disabled = false;

	@Input()
	placeholderLabel: string|null = null;
	@Input()
	resetLabel: string|null = null;

	constructor() { }

	ngOnInit(): void {
	}

	get selectedLabel(): string {
		const option = this.findSelectedOption();
		if (option) {
			return option.label;
		}

		return this.placeholderLabel;
	}

	get selectedIconClass(): string {
		const option = this.findSelectedOption();
		if (option) {
			return option.iconClass;
		}

		return null;
	}

	get selectedLabelAddition(): string {
		const option = this.findSelectedOption();
		if (option) {
			return option.labelAddition;
		}

		return null;
	}

	private findSelectedOption(): Option|null {
		return this.options.find(option => option.value === this.value) || null;
	}

	get popupOpen() {
		return !!this.popupSubscription;
	}

	toggleOpen() {
		if (this.popupOpen)  {
			this.closePopup();
			return;
		}

		const thisUp$ = fromEvent<MouseEvent>(this.dropdownElemRef.nativeElement, 'mousedown');
		const up$ = fromEvent<MouseEvent>(document, 'mouseup').pipe(takeUntil(thisUp$));
		const esc$ = fromEvent<KeyboardEvent>(document, 'keyup')
				.pipe(filter((event: KeyboardEvent) => event.key === 'Escape'));
		this.popupSubscription = merge(up$, esc$).subscribe((_mouseEvent: MouseEvent) => {
			this.closePopup();
		});
	}

	private closePopup() {
		this.popupSubscription.unsubscribe();
		this.popupSubscription = null;
	}

	get value(): string|null {
		return this._value;
	}

	@Input()
	set value(value: string|null) {
		if (this._value === value) {
			return;
		}

		this._value = value;
		this.valueChange.emit(value);
	}

	selectValue(value: string|null): void {
		if (this.popupOpen) {
			this.closePopup();
		}

		this.value = value;
	}
}

export interface Option {
	value: string;
	iconClass?: string;
	label: string;
	labelAddition?: string;
}
