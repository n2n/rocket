import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { DateTimeInComponent } from '../comp/date-time-in/date-time-in.component';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { DateTimeInModel } from '../comp/date-time-in-model';

export class DateTimeInSiField extends InSiFieldAdapter implements DateTimeInModel {

	public mandatory = false;
	public dateChoosable = true;
	public timeChoosable = true;

	constructor(public value: Date|null) {
		super();
	}

	setValue(value: Date) {
		this.value = value;
	}

	getValue(): Date {
		return this.value;
	}

	readInput(): object {
		return {
			value: (this.value ? this.value.toISOString() : null)
		};
	}

	createUiContent(): UiContent {
		return new TypeUiContent(DateTimeInComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	copyValue(): SiGenericValue {
		return new SiGenericValue(this.value);
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		if (genericValue.isInstanceOf(Date)) {
			this.setValue(genericValue.readInstance(Date));
			return Promise.resolve();
		}

		throw new GenericMissmatchError('Date expected.');
	}
}
