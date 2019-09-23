
import { SiEmbeddedEntry } from 'src/app/si/model/entity/impl/embedded/si-embedded-entry';
import { SiType } from 'src/app/si/model/entity/si-type';
import { EmbeddedEntryModel } from './embedded-entry-model';

export interface EmbeddedEntriesInModel extends EmbeddedEntryModel {

	isNonNewRemovable(): boolean;

	isSortable(): boolean;

	isSummaryRequired(): boolean;

	getPastCategory(): string|null;

	getAllowedSiTypes(): SiType[]|null;

	setValues(values: SiEmbeddedEntry[]): void;
}
