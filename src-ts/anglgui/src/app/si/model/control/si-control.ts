import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';

export interface SiControl {

	createUiContent(zone: UiZone): UiContent;
}
