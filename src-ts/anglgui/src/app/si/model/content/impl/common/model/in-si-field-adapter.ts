import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiFieldAdapter } from './si-field-adapter';
import { SiField } from '../../../si-field';

export abstract class InSiFieldAdapter extends SiFieldAdapter {

	hasInput(): boolean {
		return true;
	}

	abstract readInput(): object;

	abstract copy(): SiField;

	abstract createUiContent(): UiContent|null;
}
