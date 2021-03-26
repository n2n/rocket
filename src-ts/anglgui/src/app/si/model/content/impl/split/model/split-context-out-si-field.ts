import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiEntryBuildup } from '../../../si-entry-buildup';
import { SplitContextSiField } from './split-context-si-field';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiField } from '../../../si-field';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { Observable, of } from 'rxjs';
import {OutSiFieldAdapter} from '../../common/model/out-si-field-adapter';
import {SplitContentCollection} from './split-content-collection';

export class SplitContextOutSiField extends OutSiFieldAdapter {

	readonly collection = new SplitContentCollection();

	isDisplayable(): boolean {
		return false;
	}

	protected createUiContent(): UiContent {
		throw new IllegalSiStateError('SiField not displayable');
	}

	copyValue(): Promise<SiGenericValue> {
		this.collection.copy().then(c => new SiGenericValue(c));
	}
}
