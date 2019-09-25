import { InSiFieldAdapter } from '../in-si-field-adapter';
import { TogglerInModel } from 'src/app/ui/content/field/toggler-in-model';
import { SiContent } from '../../../structure/si-content';
import { SiField } from '../../si-field';
import { TogglerInFieldComponent } from 'src/app/ui/content/field/comp/toggler-in-field/toggler-in-field.component';
import { TypeSiContent } from '../../../structure/impl/type-si-content';

export class BooleanSiField extends InSiFieldAdapter implements TogglerInModel {

	constructor(public value = false) {
		super();
	}

	setValue(value: boolean) {
		this.value = value;
	}

	getValue(): boolean {
		return this.value;
	}

	readInput(): object {
		return {
			value: this.value
		};
	}

	copy(): SiField {
		return new BooleanSiField(this.value);
	}

	getContent(): SiContent {
		return new TypeSiContent(TogglerInFieldComponent, (ref) => {
			ref.instance.model = this;
		});
	}
}
