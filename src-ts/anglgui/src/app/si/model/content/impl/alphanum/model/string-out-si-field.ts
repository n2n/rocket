import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { StringFieldModel } from '../comp/string-field-model';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { StringOutFieldComponent } from '../comp/string-out-field/string-out-field.component';
import { SiField } from '../../../si-field';
import { SiGenericValue } from '../../../si-generic-value';


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

	isGeneric() {
		return true;
	}

	readGenericValue(): SiGenericValue {
		return new SiGenericValue(this.value === null ? null : new String(this.value));
	}

	writeGenericValue(genericValue: SiGenericValue): boolean {
		if (genericValue.isNull()) {
			this.value = null;
			return true;
		}

		if (genericValue.isInstanceOf(String)) {
			this.value = genericValue.readInstance(String).valueOf();
			return true;
		}

		return false;
	}
}
