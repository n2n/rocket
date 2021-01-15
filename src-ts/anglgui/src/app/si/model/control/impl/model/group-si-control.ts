import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

export class GroupSiControl implements SiControl {

	constructor(public siButton: SiButton, public subControls: SiControl[]) {
	}

	getSiButton(): SiButton {
		return this.siButton;
	}

	isLoading(): boolean {
		return false;
	}

	isDisabled(): boolean {
		return false;
	}

	createUiContent(uiZone: UiZone): UiContent {
		const subUiContents = this.subControls.map(c => c.createUiContent(uiZone));

		return new ButtonControlUiContent({
			getSiButton: () => this.siButton,
			isLoading: () => false,
			isDisabled: () => false,
			exec: () => {},
			getSubUiContents: () => subUiContents,
		}, uiZone);
	}

	getSubTooltip(): string|null {
		return null;
	}
}
