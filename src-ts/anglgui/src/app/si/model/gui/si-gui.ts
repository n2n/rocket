import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { SiControlBoundry } from '../control/si-control-boundry';
import { SiDeclaration } from '../meta/si-declaration';

export interface SiGui extends SiControlBoundry {

// 	getZone(): UiZone;

	createUiStructureModel(): UiStructureModel;
}
