import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { SimpleSiFieldAdapter } from './simple-si-field-adapter';

export abstract class InSiFieldAdapter extends SimpleSiFieldAdapter {

	hasInput(): boolean {
		return true;
	}

	abstract readInput(): object;

	// abstract copy(): SiField;

	// protected abstract createUiContent(uiStructure: UiStructure): UiContent;
}
