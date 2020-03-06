import { InputInFieldComponent } from '../comp/input-in-field/input-in-field.component';
import { InputInFieldModel } from '../comp/input-in-field-model';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { SiField } from '../../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiCrumbGroup } from '../../meta/model/si-crumb';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { Message } from 'src/app/util/i18n/message';
import { Fresult } from 'src/app/util/err/fresult';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';

export class NumberInSiField extends InSiFieldAdapter implements InputInFieldModel {
	public min: number|null = null;
	public max: number|null = null;
	public step = 1;
	public arrowStep: number|null = 1;
	public fixed = false;
	public mandatory = false;
	public prefixAddons: SiCrumbGroup[] = [];
	public suffixAddons: SiCrumbGroup[] = [];

	private _value: number|null = null;
	
	constructor(public label: string) {
		super();
		this.validate();
	}

	get value(): number|null {
		return this._value;
	}

	set value(value: number|null) {
		if (value === null) {
			this._value = value;
			this.validate();
			return;
		}
		
		if (this.min !== null && this.min >= value) {
			this._value = this.min;
		}

		if (this.max !== null && this.max <= value) {
			this._value = this.max;
		}

		if (this.step === 1) {
			this._value = value;
		}

		this._value = Math.round(value / this.step) * this.step;
		this.validate();
	}

	getValue(): string {
		if (this.value == null) {
			return null;
		}

		if (this.fixed) {
			return this.value.toFixed(this.countDecimals(this.step));
		}

		return this.value.toString();
	}

	setValue(valueStr: string|null) {
		if (valueStr === null) {
			this.value = null;
			return;
		}
		
		let value = parseFloat(valueStr)
		if (isNaN(value)) {
			value = null;
		}
		this.value = value;
	}

	getType(): string {
		return this.arrowStep != null ? 'number' : 'text';
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

	copy(): SiField {
		throw new Error('Method not implemented.');
	}

	isGeneric() {
		return true;
	}

	readGenericValue(): SiGenericValue {
		return new SiGenericValue(this.value === null ? null : new Number(this.value));
	}

	writeGenericValue(genericValue: SiGenericValue): Fresult<GenericMissmatchError> {
		if (genericValue.isNull()) {
			this.value = null;
			return Fresult.success();
		}

		if (genericValue.isInstanceOf(Number)) {
			this.value = genericValue.readInstance(Number).valueOf();
			return Fresult.success();
		}

		return Fresult.error(new GenericMissmatchError('Number expected.'));
	}

	createUiContent(): UiContent {
		return new TypeUiContent(InputInFieldComponent, (cr) => {
			cr.instance.model = this;
		});
	}
	
	private validate() {
		this.messages = [];

		if (this.mandatory && this.value === null) {
			this.messages.push(Message.createCode('mandatory_err', new Map([['{field}', this.label]])));
		}

		if (this.min !== null && this.value !== null && this.value < this.min) {
			this.messages.push(Message.createCode('min_err', new Map([['{field}', this.label], ['{min}', this.min.toString()]])));
		}

		if (this.max !== null && this.value !== null && this.value > this.max) {
			this.messages.push(Message.createCode('max_err', new Map([['{field}', this.label], ['{max}', this.max.toString()]])));
		}
	}
}
