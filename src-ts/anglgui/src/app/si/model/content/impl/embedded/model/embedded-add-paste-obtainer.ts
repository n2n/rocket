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
import { AddPasteObtainer } from '../comp/add-paste-obtainer';

export class EmbeddedAddPasteObtainer implements AddPasteObtainer {
	constructor(private siService: SiService, private apiUrl: string, private obtainSummary: boolean) {
	}

	private createBulkyInstruction(comp: BulkyEntrySiComp, siEntryIdentifier: SiEntryIdentifier|null): SiGetInstruction {
		if (siEntryIdentifier) {
			return SiGetInstruction.entry(comp, true, false, siEntryIdentifier.id);
		}

		return SiGetInstruction.newEntry(comp, true, false);
	}

	private createSummaryInstruction(comp: CompactEntrySiComp, siEntryIdentifier: SiEntryIdentifier|null): SiGetInstruction {
		if (siEntryIdentifier) {
			return SiGetInstruction.entry(comp, false, true, siEntryIdentifier.id);
		}

		return SiGetInstruction.newEntry(comp, false, true);
	}

	obtain(siEntryIdentifier: SiEntryIdentifier|null): Observable<SiEmbeddedEntry> {
		const request = new SiGetRequest();

		const comp = new BulkyEntrySiComp(undefined);
		request.instructions[0] = this.createBulkyInstruction(comp, siEntryIdentifier);

		let summaryComp: CompactEntrySiComp|null = null;
		if (this.obtainSummary) {
			summaryComp = new CompactEntrySiComp(undefined);
			request.instructions[1] = this.createSummaryInstruction(summaryComp, siEntryIdentifier);
		}

		return this.siService.apiGet(this.apiUrl, request).pipe(map((siGetResponse) => {
			return this.handleResponse(siGetResponse, comp, summaryComp);
		}));
	}

	private handleResponse(response: SiGetResponse, comp: BulkyEntrySiComp,
			summaryComp: CompactEntrySiComp|null): SiEmbeddedEntry {

		comp.declaration = response.results[0].declaration;
		comp.entry = response.results[0].entry;

		if (summaryComp) {
			summaryComp.declaration = response.results[1].declaration;
			summaryComp.entry = response.results[1].entry;
		}

		return new SiEmbeddedEntry(comp, summaryComp);
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

	val(siEmbeddedEntries: SiEmbeddedEntry[]) {
		const request = new SiValRequest();

		siEmbeddedEntries.forEach((siEmbeddedEntry, i) => {
			request.instructions[i] = this.createValInstruction(siEmbeddedEntry);

			siEmbeddedEntry.entry.resetError();
		});

		this.siService.apiVal(this.apiUrl, request).subscribe((response: SiValResponse) => {
			siEmbeddedEntries.forEach((siEmbeddedEntry, i) => {
				this.handleValResult(siEmbeddedEntry, response.results[i]);
			});
		});
	}

	private handleValResult(siEmbeddedEntry: SiEmbeddedEntry, siValResult: SiValResult) {
		if (siValResult.entryError) {
			siEmbeddedEntry.entry.handleError(siValResult.entryError);
		}

		if (siEmbeddedEntry.summaryComp) {
			siEmbeddedEntry.summaryComp.entry = siValResult.getResults[0].entry;
		}
	}

	private createValInstruction(siEmbeddedEntry: SiEmbeddedEntry): SiValInstruction {
		const instruction = new SiValInstruction(siEmbeddedEntry.entry.readInput());

		if (siEmbeddedEntry.summaryComp) {
			siEmbeddedEntry.summaryComp.entry = null;
			instruction.getInstructions[0] = SiValGetInstruction.create(siEmbeddedEntry.summaryComp, false, true);
		}

		return instruction;
	}
}
