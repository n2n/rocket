import { SplitOption } from '../model/split-option';
import { Observable } from 'rxjs';
import { SiField } from '../../../si-field';
import { SplitStyle } from '../model/split-context';

export interface SplitModel {

	getSplitOptions(): SplitOption[];

	getSplitStyle(): SplitStyle;

	getSiField$(key: string): Observable<SiField>;
}
