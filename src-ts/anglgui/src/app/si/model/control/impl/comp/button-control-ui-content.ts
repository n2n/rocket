import { ButtonControlModel } from './button-control-model';
import { TypeSiContent } from '../../../structure/impl/type-si-content';
import { ButtonControlComponent } from './button-control/button-control.component';

export class ButtonControlUiContent extends TypeSiContent<ButtonControlComponent> {
	constructor(model: ButtonControlModel) {
		super(ButtonControlComponent, (ref, structure) => {
			ref.instance.model = model;
			ref.instance.uiStructue = structure;
		});
	}
}
