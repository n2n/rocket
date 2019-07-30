
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { SiType } from 'src/app/si/model/content/si-type';
import { EmbeddedEntryModel } from './embedded-entry-model';

export interface EmbeddedEntryInModel extends EmbeddedEntryModel {

	isNonNewRemovable(): boolean;

	isSortable(): boolean;

	isSummaryRequired(): boolean;

	getPastCategory(): string|null;

	getAllowedSiTypes(): SiType[]|null;

	setValues(values: SiEmbeddedEntry[]): void;
}
