import { SiPageCollection } from '../model/si-page-collection';
import { SiEntryQualifierSelection } from '../model/si-entry-qualifier-selection';
import { SiControl } from '../../../control/si-control';
import { SiControlBoundry } from '../../../control/si-control-bountry';

export interface CompactExplorerModel {

	getApiUrl(): string;

	getSiPageCollection(): SiPageCollection;

	getSiControlBoundry(): SiControlBoundry;

	getSiEntryQualifierSelection(): SiEntryQualifierSelection;

	areGeneralControlsInitialized(): boolean;

	applyGeneralControls(controls: SiControl[]): void;
}
