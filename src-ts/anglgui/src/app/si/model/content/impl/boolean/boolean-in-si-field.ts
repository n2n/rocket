import { InSiFieldAdapter } from '../common/model/in-si-field-adapter';
import { SiField } from '../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TogglerInFieldComponent } from './comp/toggler-in-field/toggler-in-field.component';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { TogglerInModel } from './comp/toggler-in-model';
import { SiGenericValue } from '../../../generic/si-generic-value';
import { Fresult } from 'src/app/util/err/fresult';
import { GenericMissmatchError } from '../../../generic/generic-missmatch-error';

export class BooleanInSiField extends InSiFieldAdapter implements TogglerInModel {

	private onAsscoiatedFields: SiField[] = [];
	private offAsscoiatedFields: SiField[] = [];

	constructor(public _value = false) {
		super();
	}

	get value(): boolean {
		return this._value;
	}

	set value(value: boolean) {
		this._value = value;
	}

	addOnAssociatedField(field: SiField) {
		this.onAsscoiatedFields.push(field);
		field.setDisabled(!this.value);
	}

	addOffAssociatedField(field: SiField) {
		this.offAsscoiatedFields.push(field);
		field.setDisabled(this.value);
	}

	setValue(value: boolean) {
		this.value = value;
		this.updateAssociates();
	}

	getValue(): boolean {
		return this.value;
	}

	private updateAssociates() {
		for (const field of this.onAsscoiatedFields) {
			field.setDisabled(!this._value);
		}
		for (const field of this.offAsscoiatedFields) {
			field.setDisabled(this._value);
		}
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

	readGenericValue(): SiGenericValue {
		return new SiGenericValue(new Boolean(this.value));
	}

	writeGenericValue(genericValue: SiGenericValue): Fresult<GenericMissmatchError> {
		if (genericValue.isInstanceOf(Boolean)) {
			this.setValue(genericValue.readInstance(Boolean).valueOf());
			return Fresult.success();
		}

		return Fresult.error(new GenericMissmatchError('Boolean expected.'));
	}
}
