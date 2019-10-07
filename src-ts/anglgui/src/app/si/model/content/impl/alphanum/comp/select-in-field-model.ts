
import { MessageFieldModel } from 'src/app/si/content/field/message-field-model';

export interface SelectInFieldModel extends MessageFieldModel {

	getValue(): string|null;

	setValue(value: string|null): void;

	getOptions(): Map<string, string>;
}
