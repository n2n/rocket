import { EmbeOutCollection } from '../model/embe/embe-collection';
import { Embe } from '../model/embe/embe';

export interface EmbeddedEntriesOutModel {

	getEmbeOutCollection(): EmbeOutCollection;

	open(embe: Embe): void;

	openAll(): void;
}
