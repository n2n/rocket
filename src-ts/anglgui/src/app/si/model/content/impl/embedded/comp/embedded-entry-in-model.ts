
import { EmbeddedEntryModel } from './embedded-entry-model';
import { SiEmbeddedEntry } from '../model/si-embedded-entry';
import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';

export interface EmbeddedEntriesInModel extends EmbeddedEntryModel {

	isNonNewRemovable(): boolean;

	isSortable(): boolean;

	isSummaryRequired(): boolean;

	getPastCategory(): string|null;

	getAllowedSiTypeQualifiers(): SiTypeQualifier[]|null;

	setValues(values: SiEmbeddedEntry[]): void;
}
