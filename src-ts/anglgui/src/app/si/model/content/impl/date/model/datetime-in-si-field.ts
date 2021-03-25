import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { DateTimeInComponent } from '../comp/date-time-in/date-time-in.component';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { DateTimeInModel } from '../comp/date-time-in-model';
import { Message } from 'src/app/util/i18n/message';
import { DateUtils } from 'src/app/util/date/date-utils';

export class DateTimeInSiField extends InSiFieldAdapter implements DateTimeInModel {

	public mandatory = false;
	public dateChoosable = true;
	public timeChoosable = true;

	constructor(public label: string, public value: Date|null) {
		super();
	}

	setValue(value: Date): void {
		this.value = value;
		this.validate();
	}

	getValue(): Date {
		return this.value;
	}

	private validate(): void {
		this.resetError();

		if (this.mandatory && this.value === null) {
			this.addMessage(Message.createCode('mandatory_err', new Map([['{field}', this.label]])));
		}
	}

	readInput(): object {
		return {
			value: (this.value ? DateUtils.dateToSql(this.value) : null)
		};
	}

	createUiContent(): UiContent {
		return new TypeUiContent(DateTimeInComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	copyValue(): Promise<SiGenericValue> {
		return new SiGenericValue(this.value ? new Date(this.value) : null);
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		if (genericValue.isInstanceOf(Date)) {
			const value = genericValue.readInstance(Date);
			this.setValue(value ? new Date(value) : null);
			return Promise.resolve();
		}

		throw new GenericMissmatchError('Date expected.');
	}
}
