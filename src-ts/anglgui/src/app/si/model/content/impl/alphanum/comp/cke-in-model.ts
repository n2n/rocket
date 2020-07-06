
import { MessageFieldModel } from '../../common/comp/message-field-model';

export interface CkeInModel extends MessageFieldModel {

	getValue(): string|null;

	setValue(value: string|null): void;

	getMaxlength(): number|null;
}
