import { SiEntry } from '../content/si-entry';

export interface SiControlBoundry {

	getEntries(): SiEntry[];

	getSelectedEntries(): SiEntry[];

}
