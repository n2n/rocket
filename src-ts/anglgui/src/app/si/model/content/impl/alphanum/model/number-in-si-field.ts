import { InputInFieldComponent } from '../comp/input-in-field/input-in-field.component';
import { InputInFieldModel } from '../comp/input-in-field-model';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { SiField } from '../../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiCrumbGroup } from '../../meta/model/si-crumb';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { Message } from 'src/app/util/i18n/message';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { NumberInComponent } from '../comp/number-in/number-in.component';
import { Inject, LOCALE_ID } from '@angular/core';

export class NumberInSiField extends InSiFieldAdapter implements InputInFieldModel {
	public min: number|null = null;
	public max: number|null = null;
	public step = 1;
	public arrowStep: number|null = 1;
	public fixed = false;
	public mandatory = false;
	public prefixAddons: SiCrumbGroup[] = [];
	public suffixAddons: SiCrumbGroup[] = [];

	private pValue: number|null = null;

	private valueStr: string|null = null;
	private decimalPoint: string = null;

	constructor(public label: string, localeId: string) {
		super();
		this.validate();
		this.decimalPoint = new Intl.NumberFormat(localeId).format(1.1).replace(/1/g, '');
	}

	get value(): number|null {
		return this.pValue;
	}

	set value(value: number|null) {
		if (value === null) {
			this.pValue = value;
			this.validate();
			return;
		}

		if (!this.isInStep(value)) {
			this.pValue = this.popToStep(value);
		} else {
			this.pValue = value;
		}

		this.validate();
	}

	getValue(): string {
		if (this.value == null) {
			return null;
		}

		if (null === this.valueStr) {
			if (this.fixed) {
				this.valueStr = this.value.toFixed(this.countDecimals(this.step));
			} else {
				this.valueStr = this.value.toString();
			}

			if (this.decimalPoint !== '.') {
				this.valueStr = this.valueStr.replace('.', this.decimalPoint);
			}
		}

		return this.valueStr;
	}

	setValue(valueStr: string|null): void {
		if (null === valueStr) {
			this.valueStr = '';
			return;
		}

		this.valueStr = valueStr;
	}

	getType(): string {
		return 'text';
	}

	getMaxlength(): number {
		return null;
	}

	getMin(): number {
		return this.min;
	}

	getMax(): number {
		return this.max;
	}

	getStep(): number {
		return this.arrowStep;
	}

	private countDecimals(value: number): number {
		if ((value % 1) !== 0) {
			return value.toString().split('.')[1].length;
		}

		return 0;
	}

	getPrefixAddons(): SiCrumbGroup[] {
		return this.prefixAddons;
	}

	getSuffixAddons(): SiCrumbGroup[] {
		return this.suffixAddons;
	}

	readInput(): object {
		return {
			value: this.value
		};
	}

	copyValue(): SiGenericValue {
		return new SiGenericValue(this.value === null ? null : new Number(this.value));
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		if (genericValue.isNull()) {
			this.value = null;
			return Promise.resolve();
		}

		if (genericValue.isInstanceOf(Number)) {
			this.value = genericValue.readInstance(Number).valueOf();
			return Promise.resolve();
		}

		throw new GenericMissmatchError('Number expected.');
	}

	createUiContent(): UiContent {
		return new TypeUiContent(InputInFieldComponent, (cr) => {
			cr.instance.model = this;
		});
	}

	public onFocus(): void {
		this.messagesCollection.clear();
	}

	private isNumeric(valueStr: string): boolean {
		if (valueStr.length === 0) {
			return true;
		}

		return valueStr.match(/^-?[0-9]+(\.[0-9]*)?$/) !== null;
	}

	private clearValueStr(valueStr: string): string {
		let onlyNumbersAndDecimalPoints = valueStr.replace(/[^0-9.]/g, '');

		while (onlyNumbersAndDecimalPoints.indexOf('.') !== onlyNumbersAndDecimalPoints.lastIndexOf('.')) {
			onlyNumbersAndDecimalPoints = onlyNumbersAndDecimalPoints.substr(0,
				onlyNumbersAndDecimalPoints.lastIndexOf('.'));
		}

		return onlyNumbersAndDecimalPoints;
	}

	private getStepFactor(): number {
		return Math.pow(10, this.countDecimals(this.step));
	}

	private isInStep(value: number): boolean {
		if (this.step === null) {
			return true;
		}

		const stepFactor = this.getStepFactor();
		return (value * stepFactor) % (this.step * stepFactor) === 0;
	}

	private popToStep(value: number): number {
		const stepFactor = this.getStepFactor();

		return parseFloat((Math.round((value * stepFactor) / (this.step * stepFactor))
				* this.step).toFixed(this.countDecimals(this.step)));
	}

	private unlocalizeValue(valueStr: string): string {
		if (this.decimalPoint === '.' || valueStr.indexOf(this.decimalPoint) === -1) {
			return valueStr;
		}

		return valueStr.replace('.', '').replace(this.decimalPoint, '.');
	}

	public onBlur(): void {
		let valueStr = this.valueStr;
		this.valueStr = null;

		if (null === valueStr || valueStr.length === 0) {
			this.value = null;
			return;
		}

		valueStr = this.unlocalizeValue(valueStr);

		if (!this.isNumeric(valueStr)) {
			valueStr = this.clearValueStr(valueStr);
		}

		const parsedValue = parseFloat(valueStr);
		if (isNaN(parsedValue)) {
			this.value = null;
			return;
		}

		this.value = parsedValue;
	}

	private validate(): void {
		if (this.mandatory && this.value === null) {
			this.messagesCollection.push(Message.createCode('mandatory_err', new Map([['{field}', this.label]])));
		}

		if (this.min !== null && this.value !== null && this.value < this.min) {
			this.messagesCollection.push(Message.createCode('min_err', new Map([['{field}', this.label], ['{min}', this.min.toString()]])));
		}

		if (this.max !== null && this.value !== null && this.value > this.max) {
			this.messagesCollection.push(Message.createCode('max_err', new Map([['{field}', this.label], ['{max}', this.max.toString()]])));
		}
	}
}
