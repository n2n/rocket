import { SiPanel } from 'src/app/si/model/content/impl/embedded/si-panel';

export interface EmbeddedEntryPanelModel {

	getApiUrl(): string;

	getPanels(): SiPanel[];
}
