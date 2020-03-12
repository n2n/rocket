import { SiEntryIdentifier } from 'src/app/si/model/content/si-qualifier';
import { SiGetInstruction } from 'src/app/si/model/api/si-get-instruction';
import { Observable } from 'rxjs';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { map } from 'rxjs/operators';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { SiValRequest } from 'src/app/si/model/api/si-val-request';
import { SiValInstruction } from 'src/app/si/model/api/si-val-instruction';
import { SiValGetInstruction } from 'src/app/si/model/api/si-val-get-instruction';
import { SiValResponse } from 'src/app/si/model/api/si-val-response';
import { SiValResult } from 'src/app/si/model/api/si-val-result';
import { SiService } from 'src/app/si/manage/si.service';
import { BulkyEntrySiComp } from 'src/app/si/model/comp/impl/model/bulky-entry-si-comp';
import { CompactEntrySiComp } from 'src/app/si/model/comp/impl/model/compact-entry-si-comp';
import { SiEmbeddedEntry } from '../model/si-embedded-entry';
import { SiGetResult } from 'src/app/si/model/api/si-get-result';

export class EmbeddedEntryObtainer  {
	constructor(public siService: SiService, public apiUrl: string, public obtainSummary: boolean) {
	}

	private createBulkyInstruction(siEntryIdentifier: SiEntryIdentifier|null): SiGetInstruction {
		if (siEntryIdentifier) {
			return SiGetInstruction.entry(true, false, siEntryIdentifier.id);
		}

		return SiGetInstruction.newEntry(true, false);
	}

	private createSummaryInstruction(siEntryIdentifier: SiEntryIdentifier|null): SiGetInstruction {
		if (siEntryIdentifier) {
			return SiGetInstruction.entry(false, true, siEntryIdentifier.id);
		}

		return SiGetInstruction.newEntry(false, true);
	}

	obtain(siEntryIdentifiers: Array<SiEntryIdentifier|null>): Observable<SiEmbeddedEntry[]> {
		const request = new SiGetRequest();

		for (const siEntryIdentifier of siEntryIdentifiers) {
			request.instructions.push(this.createBulkyInstruction(siEntryIdentifier));

			if (this.obtainSummary) {
				request.instructions[1] = this.createSummaryInstruction(siEntryIdentifier);
			}
		}

		return this.siService.apiGet(this.apiUrl, request).pipe(map((siGetResponse) => {
			return this.handleResponse(siGetResponse);
		}));
	}

	private handleResponse(response: SiGetResponse): SiEmbeddedEntry[] {
		const siEmbeddedEntries = new Array<SiEmbeddedEntry>();

		let result: SiGetResult;
		while (result = response.results.shift()) {
			const siComp = new BulkyEntrySiComp(result.declaration);
			siComp.entry = result.entry;

			let summarySiComp: CompactEntrySiComp|null = null;
			if (this.obtainSummary) {
				result = response.results.shift();
				summarySiComp = new CompactEntrySiComp(result.declaration);
				summarySiComp.entry = result.entry;
			}

			siEmbeddedEntries.push(new SiEmbeddedEntry(siComp, summarySiComp));
		}

		return siEmbeddedEntries;
	}

	// val(siEmbeddedEntry: SiEmbeddedEntry) {
	// 	const request = new SiValRequest();
	// 	const instruction = request.instructions[0] = new SiValInstruction(siEmbeddedEntry.entry.readInput());

	// 	if (siEmbeddedEntry.summaryComp) {
	// 		siEmbeddedEntry.summaryComp.entry = null;
	// 		instruction.getInstructions[0] = SiValGetInstruction.create(siEmbeddedEntry.summaryComp, false, true);
	// 	}

	// 	siEmbeddedEntry.entry.resetError();

	// 	this.siService.apiVal(this.apiUrl, request, this.uiZone).subscribe((response: SiValResponse) => {
	// 		const result = response.results[0];

	// 		if (result.entryError) {
	// 			siEmbeddedEntry.entry.handleError(result.entryError);
	// 		}

	// 		if (siEmbeddedEntry.summaryComp) {
	// 			siEmbeddedEntry.summaryComp.entry = result.getResults[0].entry;
	// 		}
	// 	});
	// }
}
