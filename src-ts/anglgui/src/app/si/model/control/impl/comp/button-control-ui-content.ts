import { ButtonControlModel } from './button-control-model';
import { TypeUiContent } from '../../../structure/impl/type-si-content';
import { ButtonControlComponent } from './button-control/button-control.component';

export class ButtonControlUiContent extends TypeUiContent<ButtonControlComponent> {
	constructor(model: ButtonControlModel) {
		super(ButtonControlComponent, (ref, structure) => {
			ref.instance.model = model;
			ref.instance.uiStructue = structure;
		});
	}
}
