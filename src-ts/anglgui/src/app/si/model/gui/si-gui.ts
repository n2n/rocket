import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { SiControlBoundry } from '../control/si-control-boundry';

export interface SiGui extends SiControlBoundry {

// 	getZone(): UiZone;

	createUiStructureModel(): UiStructureModel;
}
