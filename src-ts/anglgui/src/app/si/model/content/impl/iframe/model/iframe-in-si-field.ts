import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import {IframeInComponent} from '../comp/iframe-in/iframe-in.component';
import {IframeInModel} from '../comp/iframe-in-model';


export class IframeInSiField extends InSiFieldAdapter implements IframeInModel {

  constructor(public url: string|null, public srcDoc: string|null, private formData: Map<string, string>|null) {
    super();
  }

  createUiContent(): UiContent|null {
    return new TypeUiContent(IframeInComponent, (ref) => {
      ref.instance.model = this;
    });
  }

  readInput(): object {
    var params = {};
    for (var [key, value] of this.formData) {
      params[key] = value;
    }
    return { params };
  }

  copyValue(): SiGenericValue {
    throw new Error('Not yet implemented');
  }

  pasteValue(): Promise<void> {
    throw new Error('Not yet implemented');
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
