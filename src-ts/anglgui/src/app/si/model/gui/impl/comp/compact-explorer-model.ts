import { SiPageCollection } from '../model/si-page-collection';
import { SiEntryQualifierSelection } from '../model/si-entry-qualifier-selection';
import { StructurePageManager } from './compact-explorer/structure-page-manager';

export interface CompactExplorerModel {

	// getApiUrl(): string;

	getStructurePageManager(): StructurePageManager;

	// getSiControlBoundry(): SiControlBoundry;

	getSiEntryQualifierSelection(): SiEntryQualifierSelection;

	// areGeneralControlsInitialized(): boolean;

	// applyGeneralControls(controls: SiControl[]): void;
}
