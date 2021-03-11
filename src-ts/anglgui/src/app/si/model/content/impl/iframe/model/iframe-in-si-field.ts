import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import {IframeInComponent} from '../comp/iframe-in/iframe-in.component';
import {IframeInModel} from '../comp/iframe-in-model';
import {GenericMissmatchError} from '../../../../generic/generic-missmatch-error';


export class IframeInSiField extends InSiFieldAdapter implements IframeInModel {

	constructor(public url: string|null, public srcDoc: string|null, private formData: Map<string, string>|null) {
		super();
	}

	createUiContent(): UiContent|null {
		return new TypeUiContent(IframeInComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	private formDataToObject() {
		if (this.formData == null) { return new Map<string, string>(); }

		const params = {};
		for (const [key, value] of this.formData) {
			params[key] = value;
		}
		return { params };
	}

	readInput(): object {
		return this.formDataToObject();
	}

	copyValue(): SiGenericValue {
		return new SiGenericValue(new Map(this.formData));
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		if (genericValue.isNull()) {
			this.formData = null;
			return Promise.resolve();
		}

		if (genericValue.isInstanceOf(Map)) {
			this.formData = new Map<string, string>(genericValue.readInstance(Map) as Map<string, string>);
			return Promise.resolve();
		}

		throw new GenericMissmatchError('Map expected.');
	}

	getUrl(): string|null {
		return this.url;
	}

	getSrcDoc(): string|null {
		return this.srcDoc;
	}

	getFormData(): Map<string, string> | null {
		return this.formData;
	}

	setFormData(formData: Map<string, string>): void {
		this.formData = formData;
	}
}
