import { SiFile } from './file-in-si-field';
import { FileOutFieldComponent } from '../comp/file-out-field/file-out-field.component';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { FileFieldModel } from '../comp/file-field-model';
import { SiField } from '../../../si-field';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { Fresult } from 'src/app/util/err/fresult';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';

export class FileOutSiField extends OutSiFieldAdapter implements FileFieldModel {

	constructor(public value: SiFile|null) {
		super();
	}

	getSiFile(): SiFile | null {
		return this.value;
	}

	createUiContent(): UiContent|null {
		return new TypeUiContent(FileOutFieldComponent, () => {
// 			ref.instance.model = this;
		});
	}

	// copy(): SiField {
	// 	throw new Error('Method not implemented.');
	// }

	readGenericValue(): SiGenericValue {
		throw new Error('Not yet implemented');
	}

	writeGenericValue(genericValue: SiGenericValue): Fresult<GenericMissmatchError> {
		throw new Error('Not yet implemented');
	}
}
