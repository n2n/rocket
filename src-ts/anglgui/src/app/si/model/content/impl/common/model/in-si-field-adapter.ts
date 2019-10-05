
import { SiField } from 'src/app/si/model/entity/si-field';
import { SiFieldAdapter } from 'src/app/si/model/entity/impl/si-field-adapter';
import { UiContent } from 'src/app/si/model/structure/ui-content';

export abstract class InSiFieldAdapter extends SiFieldAdapter {

	hasInput(): boolean {
		return true;
	}

	abstract readInput(): object;

	abstract copy(): SiField;

	abstract createContent(): UiContent|null;
}
