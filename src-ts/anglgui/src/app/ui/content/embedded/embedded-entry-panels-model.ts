import { SiPanel } from 'src/app/si/model/entity/impl/embedded/embedded-entry-panels-in-si-field';

export interface EmbeddedEntryPanelModel {

	isSortable(): boolean;

	getApiUrl(): string;

	getPanels(): SiPanel[];

	getPastCategory(): string|null;
}
