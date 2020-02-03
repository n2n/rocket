import { SiButton } from '../model/si-button';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';

export interface ButtonControlModel {

	getSubSiButtonMap?: () => Map<string, SiButton>;

	getSiButton(): SiButton;

	isLoading(): boolean;

	exec(uiZone: UiZone, subKey: string|null): void;
}
