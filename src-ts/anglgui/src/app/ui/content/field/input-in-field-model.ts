
import { MessageFieldModel } from 'src/app/ui/content/field/message-field-model';

export interface InputInFieldModel extends MessageFieldModel {

	getValue(): string|null;

	setValue(value: string|null);

	getType(): string;

	getMaxlength(): number|null;

	getMin(): number|null;

	getMax(): number|null;

	getStep(): number|null;
}
