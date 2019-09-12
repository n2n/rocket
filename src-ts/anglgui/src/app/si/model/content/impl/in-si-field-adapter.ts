
import { SiField } from 'src/app/si/model/content/si-field';
import { SiFieldAdapter } from 'src/app/si/model/content/impl/si-field-adapter';
import { SiContent } from 'src/app/si/model/structure/si-content';

export abstract class InSiFieldAdapter extends SiFieldAdapter {

	hasInput(): boolean {
		return true;
	}

	abstract readInput(): object;

	abstract copy(): SiField;

	abstract getContent(): SiContent|null;
}
