import { SiPageCollection } from '../model/si-page-collection';
import { SiEntryQualifierSelection } from '../model/si-entry-qualifier-selection';

export interface CompactExplorerModel {

	// getApiUrl(): string;

	getSiPageCollection(): SiPageCollection;

	// getSiControlBoundry(): SiControlBoundry;

	getSiEntryQualifierSelection(): SiEntryQualifierSelection;

	// areGeneralControlsInitialized(): boolean;

	// applyGeneralControls(controls: SiControl[]): void;
}
