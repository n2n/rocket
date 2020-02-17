import { SiPanel } from '../model/si-panel';
import { AddPasteObtainer } from './add-paste-obtainer';

export interface EmbeddedEntryPanelInModel {

	getObtainer(): AddPasteObtainer;

	getPanels(): SiPanel[];
}
