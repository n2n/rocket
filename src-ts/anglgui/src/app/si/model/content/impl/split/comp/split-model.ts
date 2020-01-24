import { SplitOption } from '../model/split-option';
import { SiField } from '../../../si-field';
import { SplitStyle } from '../model/split-context';

export interface SplitModel {

	getSplitOptions(): SplitOption[];

	getSplitStyle(): SplitStyle;
	
	isKeyActive(key: string): boolean;
	
	activateKey(key: string);

	getSiField$(key: string): Promise<SiField>;
}
