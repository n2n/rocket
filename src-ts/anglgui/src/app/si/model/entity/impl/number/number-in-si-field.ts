import { InputInFieldModel } from "src/app/ui/content/field/input-in-field-model";

export class NumberInSiField extends InFieldAdapter implements InputInFieldModel {
	public min: number|null = null;
	public max: number|null = null;
	public step: number|null = null;
	public decimalPlacesNum: number|null = null;
	public fixed = false;

	private value: number = null;

	getValue(): string {
		if (this.value === null) {
			return null;
		}
		
		if (this.fixed && this.decimalPlacesNum !== null) {
			return this.value.toFixed(this.decimalPlacesNum);
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
		
		if (this.decimalPlacesNum !== null) {
			const fac = Math.pow(10, this.decimalPlacesNum); 
			this.value = Math.round(value * fac) / fac;
			return;
		}
		
		if (this.step !== null) {
			this.value = Math.round(value / this.step) * this.step;
			return;
		}
		
		this.value = value;
	}
	
	getType(): string {
		return 'number';
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
		if (this.step !== null) {
			return this.step;
		}
		
		if (this.decimalPlacesNum !== null) {
			return 1 / Math.pow(10, this.decimalPlacesNum);
		}
		
		return 1;
	}	
}
