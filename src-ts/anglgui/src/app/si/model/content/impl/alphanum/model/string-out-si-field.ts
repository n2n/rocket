import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { StringFieldModel } from '../comp/string-field-model';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { StringOutFieldComponent } from '../comp/string-out-field/string-out-field.component';
import { Fresult } from 'src/app/util/err/fresult';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';


export class StringOutSiField extends OutSiFieldAdapter {

	constructor(private value: string|null) {
		super();
	}

	createUiContent(uiStructure: UiStructure): UiContent|null {
		return new TypeUiContent(StringOutFieldComponent, (ref) => {
			ref.instance.model = {
				getMessages: () => this.messagesCollection.get(),
				getValue: () => this.value,
				isBulky: () => !!uiStructure.type
			};
		});
	}

	getValue(): string | null {
		return this.value;
	}

	// copy(): SiField {
	// 	return new StringOutSiField(this.value);
	// }

	isGeneric() {
		return true;
	}

	copyValue(): SiGenericValue {
		return new SiGenericValue(this.value === null ? null : new String(this.value));
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		if (genericValue.isNull()) {
			this.value = null;
			return Promise.resolve();
		}

		if (genericValue.isInstanceOf(String)) {
			this.value = genericValue.readInstance(String).valueOf();
			return Promise.resolve();
		}

		throw new GenericMissmatchError('String expected.');
	}
}
