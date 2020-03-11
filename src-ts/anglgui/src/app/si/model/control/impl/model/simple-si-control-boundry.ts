import { SiControlBoundry } from '../../si-control-bountry';
import { SiEntry } from '../../../content/si-entry';

export class SimpleSiControlBoundry implements SiControlBoundry {

	constructor(public entries: SiEntry[]) {
	}

	getEntries(): SiEntry[] {
		return this.entries;
	}

	getSelectedEntries(): SiEntry[] {
		return this.entries;
	}
}
