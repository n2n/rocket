import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { SiCrumbGroup } from './si-crumb';
import { CrumbFieldModel } from './crumb-field-model';
import { CrumbOutFieldComponent } from '../comp/crumb-out-field/crumb-out-field.component';

export class CrumbOutSiField extends OutSiFieldAdapter implements CrumbFieldModel {

	constructor(public crumbGroups: SiCrumbGroup[]) {
		super();
	}

	createUiContent(): UiContent|null {
		return new TypeUiContent(CrumbOutFieldComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	// copy(): SiField {
	// 	throw new Error('Method not implemented.');
	// }

	copyValue(): SiGenericValue {
		throw new Error('Not yet implemented');
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		throw new Error('Not yet implemented');
	}

	getSiCrumbGroups(): SiCrumbGroup[] {
		return this.crumbGroups;
	}
}
