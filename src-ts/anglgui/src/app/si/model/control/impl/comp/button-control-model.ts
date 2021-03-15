import { SiButton } from '../model/si-button';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

export interface ButtonControlModel {

	getSubUiContents?: () => UiContent[];

	getSubSiButtonMap?: () => Map<string, SiButton>;

	getSubTooltip?: () => string|null;

	getSiButton(): SiButton;

	isLoading(): boolean;

	isDisabled(): boolean;

	getUiZone(): UiZone;

  /**
   * when Aconfig present <a> tag used
   */
	getAconfig?: () => Aconfig|null;

  /**
   * when exec returns true click event will be prevented
   * @param subKey
   */
	exec(subKey: string|null): boolean;
}

export interface Aconfig {
  url: string;
  routerLinked: boolean;
  newWindow?: boolean;
}
