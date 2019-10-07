import { OutSiFieldAdapter } from '../common/model/out-si-field-adapter';
import { StringFieldModel } from '../alphanum/comp/string-field-model';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/impl/type-si-content';
import { StringOutFieldComponent } from '../alphanum/comp/string-out-field/string-out-field.component';
import { SiField } from '../../si-field';

export class StringOutSiField extends OutSiFieldAdapter implements StringFieldModel {

	constructor(private value: string|null) {
		super();
	}

	createUiContent(): UiContent|null {
		return new TypeUiContent(StringOutFieldComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	getValue(): string | null {
		return this.value;
	}

	copy(): SiField {
		return new StringOutSiField(this.value);
	}
}
