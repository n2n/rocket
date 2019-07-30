import { SiService } from 'src/app/si/model/si.service';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { BulkyEntrySiComp } from 'src/app/si/model/structure/impl/bulky-entry-si-content';
import { SiIdentifier } from 'src/app/si/model/content/si-qualifier';
import { SiGetInstruction } from 'src/app/si/model/api/si-get-instruction';
import { CompactEntrySiComp } from 'src/app/si/model/structure/impl/compact-entry-si-content';
import { Observable } from 'rxjs';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { map } from 'rxjs/operators';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { AddPasteObtainer } from 'src/app/ui/control/comp/add-paste/add-paste.component';

export class EmbeddedAddPasteObtainer implements AddPasteObtainer {
	constructor(private siService: SiService, private apiUrl: string, private siZone: SiZone,
			private optainSummary: boolean) {
	}

	private createBulkyInstruction(comp: BulkyEntrySiComp, siIdentifier: SiIdentifier|null): SiGetInstruction {
		if (siIdentifier) {
			return SiGetInstruction.entry(comp, true, false, siIdentifier.id);
		}

		return SiGetInstruction.newEntry(comp, true, false);
	}

	private createSummaryInstruction(comp: CompactEntrySiComp, siIdentifier: SiIdentifier|null): SiGetInstruction {
		if (siIdentifier) {
			return SiGetInstruction.entry(comp, false, true, siIdentifier.id);
		}

		return SiGetInstruction.newEntry(comp, false, true);
	}

	obtain(siIdentifier: SiIdentifier|null): Observable<SiEmbeddedEntry> {
		const request = new SiGetRequest();

		const comp = new BulkyEntrySiComp(undefined, undefined);
		request.instructions[0] = this.createBulkyInstruction(comp, siIdentifier);

		let summaryComp: CompactEntrySiComp|null = null;
		if (this.optainSummary) {
			summaryComp = new CompactEntrySiComp(undefined, undefined);
			request.instructions[1] = this.createSummaryInstruction(summaryComp, siIdentifier);
		}

		return this.siService.apiGet(this.apiUrl, request, this.siZone).pipe(map((siGetResponse) => {
			return this.handleResponse(siGetResponse, comp, summaryComp);
		}));
	}

	private handleResponse(response: SiGetResponse, comp: BulkyEntrySiComp,
			summaryComp: CompactEntrySiComp|null): SiEmbeddedEntry {

		comp.bulkyDeclaration = response.results[0].bulkyDeclaration;
		comp.entry = response.results[0].entry;

		if (summaryComp) {
			summaryComp.compactDeclaration = response.results[1].compactDeclaration;
			summaryComp.entry = response.results[1].entry;
		}

		return new SiEmbeddedEntry(comp, summaryComp);
	}
}
