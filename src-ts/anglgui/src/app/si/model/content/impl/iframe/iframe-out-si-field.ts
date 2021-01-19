import {OutSiFieldAdapter} from "../common/model/out-si-field-adapter";
import {UiContent} from "../../../../../ui/structure/model/ui-content";
import {TypeUiContent} from "../../../../../ui/structure/model/impl/type-si-content";
import {IframeComponent} from "../../../../../ui/util/comp/iframe/iframe.component";
import {SiGenericValue} from "../../../generic/si-generic-value";

export class IframeOutSiField extends OutSiFieldAdapter {

  constructor(public srcDoc: String|null) {
    super();
  }

  getSrcDoc(): String | null {
    return this.srcDoc;
  }

  createUiContent(): UiContent|null {
    return new TypeUiContent(IframeComponent, (ref) => {
			ref.instance.srcDoc = this.srcDoc;
    });
  }

  copyValue(): SiGenericValue {
    throw new Error('Not yet implemented');
  }

  pasteValue(genericValue: SiGenericValue): Promise<void> {
    throw new Error('Not yet implemented');
  }
}
