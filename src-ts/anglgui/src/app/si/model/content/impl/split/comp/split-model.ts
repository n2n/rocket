import { SplitOption } from '../model/split-option';
import { SiField } from '../../../si-field';
import { SplitStyle } from '../model/split-context-si-field';

export interface SplitModel {

	getSplitOptions(): SplitOption[];

	getSplitStyle(): SplitStyle;

	isKeyActive(key: string): boolean;

	activateKey(key: string): void;

	getSiField$(key: string): Promise<SiField>;

	getCopyTooltip(): string|null;
}
