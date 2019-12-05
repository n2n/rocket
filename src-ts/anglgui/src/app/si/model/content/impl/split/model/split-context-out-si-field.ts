import { SiField } from '../../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';

export class SplitContextOutSiField extends OutSiFieldAdapter {

	readInput(): object {
		throw new Error('Method not implemented.');
	}

	copy(): SiField {
		throw new Error('Method not implemented.');
	}

	protected createUiContent(): UiContent {
		throw new Error('Method not implemented.');
	}

}
