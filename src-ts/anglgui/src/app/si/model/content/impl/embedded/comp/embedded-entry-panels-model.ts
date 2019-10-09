import { SiPanel } from '../model/si-panel';

export interface EmbeddedEntryPanelModel {

	getApiUrl(): string;

	getPanels(): SiPanel[];
}
