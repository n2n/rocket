import { SiPageCollection } from '../model/si-page-collection';
import { SiComp } from '../../si-comp';
import { SiEntryQualifierSelection } from '../model/si-entry-qualifier-selection';

export interface EntriesListModel {

	getApiUrl(): string;

	getSiPageCollection(): SiPageCollection;

	getSiComp(): SiComp;

	getSiEntryQualifierSelection(): SiEntryQualifierSelection;
}
