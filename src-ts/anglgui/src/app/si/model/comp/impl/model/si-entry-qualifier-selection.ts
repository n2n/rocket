import { SiEntryQualifier } from '../../../content/si-qualifier';

export interface SiEntryQualifierSelection {
	min: number;
	max: number|null;
	selectedQualfiers: SiEntryQualifier[];
	done: () => any;
	cancel: () => any;
}
