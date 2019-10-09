import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { SelectInFieldModel } from '../comp/select-in-field-model';
import { SiField } from '../../../si-field';
import { SelectInFieldComponent } from '../comp/select-in-field/select-in-field.component';

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

	createUiContent(): UiContent {
		return new TypeUiContent(SelectInFieldComponent, (ref) => {
			ref.instance.model = this;
		});
	}
}
