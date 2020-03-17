import { SiEntryIdentifier } from '../../../si-qualifier';
import { SiEmbeddedEntry } from '../model/si-embedded-entry';
import { Observable } from 'rxjs';

export interface AddPasteObtainer {

	obtain: (siEntryIdentifier: SiEntryIdentifier|null) => Observable<SiEmbeddedEntry>;
}
