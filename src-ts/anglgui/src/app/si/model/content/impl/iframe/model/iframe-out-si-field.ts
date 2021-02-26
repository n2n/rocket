import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { IframeOutComponent } from '../comp/iframe-out/iframe-out.component';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import {IframeOutModel} from '../comp/iframe-out-model';

export class IframeOutSiField extends OutSiFieldAdapter implements IframeOutModel {

	constructor(public url: string|null, public srcDoc: string|null) {
		super();
	}

	getUrl(): string|null {
		return this.url;
	}

	getSrcDoc(): string|null {
		return this.srcDoc;
	}

	createUiContent(): UiContent|null {
		return new TypeUiContent(IframeOutComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	copyValue(): SiGenericValue {
		throw new Error('Not yet implemented');
	}

	pasteValue(): Promise<void> {
		throw new Error('Not yet implemented');
	}
}
