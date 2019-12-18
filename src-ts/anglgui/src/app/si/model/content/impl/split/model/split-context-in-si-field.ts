import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { SiField } from '../../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

export class SplitContextInSiField extends InSiFieldAdapter {

	readInput(): object {
		const entryInputObj = {};
		for (const splitOption of this.getSplitOptions()) {
			if (entry = splitOption.getLoadedSiEntry()) {
				entryInputObj[splitOption.key] = entry.readInput();
			}
		}
	}

	copy(): SiField {
		throw new Error('Method not implemented.');
	}

	protected createUiContent(): UiContent {
		throw new Error('Method not implemented.');
	}
}
