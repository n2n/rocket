
import { MessageFieldModel } from '../../common/comp/message-field-model';
import { CkeMode } from '../model/cke-in-si-field';

export interface CkeInModel extends MessageFieldModel {

	getValue(): string|null;

	setValue(value: string|null): void;

	getMaxlength(): number|null;

	getCkeMode(): CkeMode;

	getBodyId(): string|null;

	getBodyClass(): string|null;
}
