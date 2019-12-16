import { SplitOption } from '../model/split-option';
import { Observable } from 'rxjs';
import { SiField } from '../../../si-field';

export interface SplitModel {

	getSplitOptions(): SplitOption[];

	getSiField$(key: string): Observable<SiField>;
}
