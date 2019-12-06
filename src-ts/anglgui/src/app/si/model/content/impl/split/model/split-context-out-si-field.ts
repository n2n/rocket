import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { SiEntryBuildup } from '../../../si-entry-buildup';

export class SplitContextOutSiField extends OutSiFieldAdapter {

	copy(entryBuildUp: SiEntryBuildup) {
		throw new Error('Method not implemented.');
	}

	protected createUiContent(): UiContent {
		throw new Error('Method not implemented.');
	}

}
