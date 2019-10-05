import { SiPanel } from 'src/app/si/model/entity/impl/embedded/si-panel';

export interface EmbeddedEntryPanelModel {

	getApiUrl(): string;

	getPanels(): SiPanel[];
}
