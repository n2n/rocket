import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { SiControlBoundary } from '../control/si-control-boundary';
import { SiDeclaration } from '../meta/si-declaration';

export interface SiGui extends SiControlBoundary {

// 	getZone(): UiZone;

	createUiStructureModel(): UiStructureModel;
}
