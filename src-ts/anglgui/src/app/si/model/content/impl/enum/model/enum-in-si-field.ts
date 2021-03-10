import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { SelectInFieldModel } from '../comp/select-in-field-model';
import { SiField } from '../../../si-field';
import { SelectInFieldComponent } from '../comp/select-in-field/select-in-field.component';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { Message } from 'src/app/util/i18n/message';

export class EnumInSiField extends InSiFieldAdapter implements SelectInFieldModel {
	public mandatory = false;
	private asscoiatedFieldsMap = new Map<string, SiField[]>();

	constructor(public label: string, public value: string|null, public options: Map<string, string>) {
		super();
	}

	getValue(): string {
		return this.value;
	}

	setValue(value: string): void {
		this.value = value;
		this.updateAssociates();
		this.validate();
	}

	private validate() {
		this.resetError();

		if (this.mandatory && this.value === null) {
			this.addMessage(Message.createCode('mandatory_err', new Map([['{field}', this.label]])));
		}
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

	// copy(): SiField {
	// 	const copy = new EnumInSiField(this.label, this.value, this.options);
	// 	copy.mandatory = this.mandatory;
	// 	return copy;
	// }

	protected createUiContent(): UiContent {
		return new TypeUiContent(SelectInFieldComponent, (ref) => {
			ref.instance.model = this;
		});
	}

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

	setAssociatedFields(value: string, fields: SiField[]) {
		this.asscoiatedFieldsMap.set(value, fields);
		fields.forEach(field => field.setDisabled(this.value !== value));
	}

	private updateAssociates() {
		for (const [aKey, aFields] of this.asscoiatedFieldsMap) {
			const disabled = aKey !== this.value;
			aFields.forEach(field => field.setDisabled(disabled));
		}
	}
}
