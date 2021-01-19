import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';
import { ButtonControlModel } from '../comp/button-control-model';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

export class SimpleSiControl implements SiControl, ButtonControlModel {
	public disabled = false;

	constructor(public siButton: SiButton, public callback: () => any) {
	}

	getSiButton(): SiButton {
		return this.siButton;
	}

	isLoading(): boolean {
		return false;
	}

	isDisabled(): boolean {
		return this.disabled;
	}

	exec(): void {
		this.callback();
	}

	createUiContent(uiZone: UiZone): UiContent {
		return new ButtonControlUiContent(this, uiZone);
	}

	getSubTooltip(): string|null {
		return null;
	}
}
