import { Component, OnInit, ViewChild, ElementRef, Input, Output, EventEmitter } from '@angular/core';
import { Subscription, fromEvent, merge } from 'rxjs';
import { filter, takeUntil } from 'rxjs/operators';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';

@Component({
	selector: 'rocket-ui-select',
	templateUrl: './select.component.html',
	styleUrls: ['./select.component.css']
})
export class SelectComponent implements OnInit {
	private static idCounter = 0;
	private _value: string|null = null;
	private popupSubscription: Subscription|null = null;

	// @ViewChild('dropdown', { static: true })
	// private dropdownElemRef: ElementRef;

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

	id: string;

	constructor(private elemRef: ElementRef) { 
		this.id = 'rocket-dropdown-' + (SelectComponent.idCounter++);
	}

	ngOnInit(): void {
	}

	get selectedLabel(): string|null {
		const option = this.findSelectedOption();
		if (option) {
			return option.label;
		}

		return this.placeholderLabel;
	}

	get selectedIconClass(): string|null {
		const option = this.findSelectedOption();
		if (option) {
			return option.iconClass || null;
		}

		return null;
	}

	get selectedLabelAddition(): string|null {
		const option = this.findSelectedOption();
		if (option) {
			return option.labelAddition || null;
		}

		return null;
	}

	get empty(): boolean {
		return !this.findSelectedOption();
	}

	private findSelectedOption(): Option|null {
		return this.options.find(option => option.value === this.value) || null;
	}

	get popupOpen() {
		return !!this.popupSubscription;
	}

	toggleOpen() {
		if (this.popupOpen)	{
			this.closePopup();
			return;
		}

		if (this.popupSubscription) {
			throw new IllegalStateError('');
		}

		const up$ = fromEvent<MouseEvent>(document, 'click').pipe(filter(e => !this.elemRef.nativeElement.contains(e.target)));
		const esc$ = fromEvent<KeyboardEvent>(document, 'keyup')
				.pipe(filter((event: KeyboardEvent) => event.key === 'Escape'));
		this.popupSubscription = merge(up$, esc$).subscribe(() => {
			this.closePopup();
		});
	}

	private closePopup() {
		if (!this.popupSubscription) {
			return;
		}

		this.popupSubscription.unsubscribe();
		this.popupSubscription = null;
	}

	get value(): string|null {
		return this._value;
	}

	@Input()
	set value(value: string|null) {
		this._value = value;
	}

	selectValue(value: string|null): void {
		if (this.popupOpen) {
			this.closePopup();
		}

		if (this._value === value) {
			return;
		}

		this.value = value;
		this.valueChange.emit(value);
	}
}

export interface Option {
	value: string;
	iconClass?: string;
	label: string;
	labelAddition?: string;
}
