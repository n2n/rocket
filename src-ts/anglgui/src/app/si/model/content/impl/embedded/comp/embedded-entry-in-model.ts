
import { EmbeddedEntryModel } from './embedded-entry-model';
import { SiEmbeddedEntry } from '../model/si-embedded-entry';
import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';
import { AddPasteObtainer } from './add-paste-obtainer';

export interface EmbeddedEntriesInModel extends EmbeddedEntryModel {

	isNonNewRemovable(): boolean;

	isSortable(): boolean;

	isSummaryRequired(): boolean;

	getPastCategory(): string|null;

	getAllowedSiTypeQualifiers(): SiTypeQualifier[]|null;

	setValues(values: SiEmbeddedEntry[]): void;

	getObtainer(): AddPasteObtainer;
}
