import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { SiCrumbGroup } from './si-crumb';
import { CrumbFieldModel } from './crumb-field-model';
import { CrumbOutFieldComponent } from '../comp/crumb-out-field/crumb-out-field.component';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

class SiCrumbGroupCollection  {
	constructor(public crumbGroups: SiCrumbGroup[]) {
	}
}

export class CrumbOutSiField extends OutSiFieldAdapter implements CrumbFieldModel {

	constructor(public crumbGroups: SiCrumbGroup[]) {
		super();
	}

	createUiContent(uiStructure: UiStructure): UiContent|null {
		return new TypeUiContent(CrumbOutFieldComponent, (ref) => {
			ref.instance.model = this;
			// ref.instance.compact = uiStructure.compact;
		});
	}

	// copy(): SiField {
	// 	throw new Error('Method not implemented.');
	// }

	isGeneric() {
		return true;
	}

	copyValue(): SiGenericValue {
		return new SiGenericValue(new SiCrumbGroupCollection(this.crumbGroups));
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		this.crumbGroups = genericValue.readInstance(SiCrumbGroupCollection).crumbGroups;
		return Promise.resolve();
	}

	getSiCrumbGroups(): SiCrumbGroup[] {
		return this.crumbGroups;
	}
}
