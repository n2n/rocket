import { InputInFieldComponent } from '../comp/input-in-field/input-in-field.component';
import { InputInFieldModel } from '../comp/input-in-field-model';
import { SiCrumbGroup } from '../../meta/si-crumb';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { SiField } from '../../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/impl/type-si-content';

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

	get value(): number|null {
		return this._value;
	}

	set value(value: number|null) {
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
		}

		this.value = parseFloat(valueStr);
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
		throw new Error('Method not implemented.');
	}

	copy(): SiField {
		throw new Error('Method not implemented.');
	}

	createUiContent(): UiContent {
		return new TypeUiContent(InputInFieldComponent, (cr) => {
			cr.instance.model = this;
		});
	}
}
