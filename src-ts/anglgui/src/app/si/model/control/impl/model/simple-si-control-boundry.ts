import { SiControlBoundry } from '../../si-control-bountry';
import { SiEntry } from '../../../content/si-entry';

export class SimpleSiControlBoundry implements SiControlBoundry {

	constructor(public entries: SiEntry[]) {
	}

	getControlledEntries(): SiEntry[] {
		return this.entries;
	}
}
