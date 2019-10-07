import { InSiFieldAdapter } from '../common/model/in-si-field-adapter';
import { SiField } from '../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TogglerInFieldComponent } from './comp/toggler-in-field/toggler-in-field.component';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { TogglerInModel } from './comp/toggler-in-model';

export class BooleanInSiField extends InSiFieldAdapter implements TogglerInModel {

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
		return new BooleanInSiField(this.value);
	}

	createUiContent(): UiContent {
		return new TypeUiContent(TogglerInFieldComponent, (ref) => {
			ref.instance.model = this;
		});
	}
}
