import { InSiFieldAdapter } from '../in-si-field-adapter';
import { SelectInFieldModel } from 'src/app/ui/content/field/select-in-field-model';
import { SiField } from '../../si-field';
import { UiContent } from '../../../structure/ui-content';
import { TypeSiContent } from '../../../structure/impl/type-si-content';
import { SelectInFieldComponent } from 'src/app/ui/content/field/comp/select-in-field/select-in-field.component';

export class EnumInSiField extends InSiFieldAdapter implements SelectInFieldModel {
	public mandatory = false;

	constructor(public value: string|null, public options: Map<string, string>) {
		super();
	}

	getValue(): string {
		return this.value;
	}

	setValue(value: string): void {
		this.value = value;
	}

	getOptions(): Map<string, string> {
		return this.options;
	}

	isMandatory(): boolean {
		return this.mandatory;
	}

	readInput(): object {
		return {
			value: this.value
		};
	}

	copy(): SiField {
		const copy =	new EnumInSiField(this.value, this.options);
		copy.mandatory = this.mandatory;
		return copy;
	}

	createContent(): UiContent {
		return new TypeSiContent(SelectInFieldComponent, (ref) => {
			ref.instance.model = this;
		});
	}


}
