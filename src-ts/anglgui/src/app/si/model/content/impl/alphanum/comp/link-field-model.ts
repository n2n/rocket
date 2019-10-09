import { MessageFieldModel } from '../../common/comp/message-field-model';

export interface LinkOutModel extends MessageFieldModel {
	isHref(): boolean;

	getRef(): string;

	getLabel(): string;
}
