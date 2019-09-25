import { InputInFieldModel } from 'src/app/ui/content/field/input-in-field-model';
import { InSiFieldAdapter } from '../in-si-field-adapter';
import { TypeSiContent } from '../../../structure/impl/type-si-content';
import { InputInFieldComponent } from 'src/app/ui/content/field/comp/input-in-field/input-in-field.component';
import { SiContent } from '../../../structure/si-content';
import { SiField } from '../../si-field';

export class NumberInSiField extends InSiFieldAdapter implements InputInFieldModel {

	public min: number|null = null;
	public max: number|null = null;
	public step = 1;
	public arrowStep: number|null = 1;
	public fixed = false;
	public mandatory = false;

	private value: number = null;

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

		const value = parseFloat(valueStr);

		if (this.min !== null && this.min >= value) {
			this.value = this.min;
		}

		if (this.max !== null && this.max <= value) {
			this.value = this.max;
		}

		if (this.step === 1) {
			this.value = value;
		}

		this.value = Math.round(value / this.step) * this.step;
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

	readInput(): object {
		throw new Error('Method not implemented.');
	}

	copy(): SiField {
		throw new Error('Method not implemented.');
	}

	getContent(): SiContent {
		return new TypeSiContent(InputInFieldComponent, (cr) => {
			cr.instance.model = this;
		});
	}
}
