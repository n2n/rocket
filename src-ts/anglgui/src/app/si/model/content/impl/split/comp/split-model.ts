import { SplitContent } from '../model/split-context';
import { SplitOption } from '../model/split-option';

export interface SplitModel {

	getSplitOptions(): SplitOption[];
}
