
import { EmbeddedEntryModel } from './embedded-entry-model';
import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';
import { AddPasteObtainer } from './add-paste-obtainer';
import { EmbeInCollection } from '../model/embe-collection';
import { Embe } from '../model/embe';

export interface EmbeddedEntriesInModel extends EmbeddedEntryModel {

	// isNonNewRemovable(): boolean;

	isSortable(): boolean;

	// isSummaryRequired(): boolean;

	// getTypeCategory(): string;

	getAllowedSiTypeQualifiers(): SiTypeQualifier[]|null;

	getAddPasteObtainer(): AddPasteObtainer;

	getEmbeInCollection(): EmbeInCollection;

	open(embe: Embe): void;

	openAll(): void;
}
