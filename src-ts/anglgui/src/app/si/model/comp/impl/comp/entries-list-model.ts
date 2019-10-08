import { SiPage } from '../model/si-page';
import { SiDeclaration } from '../../../meta/si-declaration';

export interface EntriesListModel {

	getApiUrl(): string;

	getSiDeclaration(): SiDeclaration|null;

	setSiDeclaration(siDeclaration: SiDeclaration|null): void;

	getSiPages(): SiPage[];

	addSiPage(siPage: SiPage): void;
}
