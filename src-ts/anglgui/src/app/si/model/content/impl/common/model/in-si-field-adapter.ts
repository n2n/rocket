import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiFieldAdapter } from './si-field-adapter';
import { SiField } from '../../../si-field';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

export abstract class InSiFieldAdapter extends SiFieldAdapter {

	hasInput(): boolean {
		return true;
	}

	abstract readInput(): object;

	abstract copy(): SiField;

	protected abstract createUiContent(uiStructure: UiStructure): UiContent;
}
