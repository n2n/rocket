import { AddPasteObtainer } from './add-paste-obtainer';
import { EmbeInCollection } from '../model/embe/embe-collection';
import { Embe } from '../model/embe/embe';

export interface EmbeddedEntriesInModel {

	getMin(): number;

	getMax(): number|null;

	// isNonNewRemovable(): boolean;

	isSortable(): boolean;

	// isSummaryRequired(): boolean;

	// getTypeCategory(): string;

	// getAllowedSiMaskQualifiers(): SiMaskQualifier[]|null;

	getAddPasteObtainer(): AddPasteObtainer;

	getEmbeInCollection(): EmbeInCollection;

	open(embe: Embe): void;

	openAll(): void;
}
