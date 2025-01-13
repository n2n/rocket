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
import { BulkyEntrySiGui } from 'src/app/si/model/gui/impl/model/bulky-entry-si-gui';
import { CompactEntrySiGui } from 'src/app/si/model/gui/impl/model/compact-entry-si-gui';
import { SiEmbeddedEntry } from './si-embedded-entry';
import { SiGetResult } from 'src/app/si/model/api/si-get-result';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { SiModStateService } from 'src/app/si/model/mod/model/si-mod-state.service';

export class EmbeddedEntryObtainer	{

	constructor(public siService: SiService, public siModStateService: SiModStateService, public siFrame: SiFrame,
			public bulkyContextMaskId: string, public summaryContextMaskId: string|null,
			public allowedMaskIds: Array<string>|null) {
	}

	private preloadedNew$: Promise<SiEmbeddedEntry>|null = null;

	private createBulkyInstruction(siEntryIdentifier: /*SiEntryIdentifier|*/null): SiGetInstruction {
		// if (siEntryIdentifier) {
		// 	return SiGetInstruction.entryFromIdentifier(siEntryIdentifier);
		// }

		return SiGetInstruction.newEntry(this.bulkyContextMaskId).setAllowedMaskIds(this.allowedMaskIds);
	}

	private createSummaryInstruction(siEntryIdentifier: /*SiEntryIdentifier|*/null): SiGetInstruction {
		// if (siEntryIdentifier) {
		// 	return SiGetInstruction.entryFromIdentifier(eiEntryIdentifier);
		// }

		return SiGetInstruction.newEntry(this.summaryContextMaskId!).setAllowedMaskIds(this.allowedMaskIds);
	}

	preloadNew(): void {
		if (this.preloadedNew$) {
			return;
		}

		this.preloadedNew$ = this.obtain([null])
				.pipe(map(siEmbeddedEntries => siEmbeddedEntries[0] as any)).toPromise();
	}

	obtainNew(): Promise<SiEmbeddedEntry> {
		this.preloadNew();
		const siEmbeddedEntry$ = this.preloadedNew$!;
		this.preloadedNew$ = null;
		this.preloadNew();
		return siEmbeddedEntry$;
	}

	obtain(siEntryIdentifiers: Array</*SiEntryIdentifier|*/null>): Observable<SiEmbeddedEntry[]> {
		const request = new SiGetRequest();

		for (const siEntryIdentifier of siEntryIdentifiers) {
			request.instructions.push(this.createBulkyInstruction(siEntryIdentifier));

			if (this.summaryContextMaskId !== null) {
				request.instructions[1] = this.createSummaryInstruction(siEntryIdentifier);
			}
		}

		return this.siService.apiGet(this.siFrame.apiUrl, request).pipe(map((siGetResponse) => {
			return this.handleResponse(siGetResponse);
		}));
	}

	private handleResponse(response: SiGetResponse): SiEmbeddedEntry[] {
		const siEmbeddedEntries = new Array<SiEmbeddedEntry>();

		let result: SiGetResult|undefined;
		while (result = response.instructionResults.shift()) {
			const siComp = new BulkyEntrySiGui(this.siFrame, this.siService, this.siModStateService);
			siComp.declaration = result.declaration!
			siComp.valueBoundary = result.valueBoundary;

			let summarySiGui: CompactEntrySiGui|null = null;
			if (this.summaryContextMaskId !== null) {
				result = response.instructionResults.shift()!;
				summarySiGui = new CompactEntrySiGui(this.siFrame, this.siService, this.siModStateService);
				summarySiGui.declaration = result.declaration!
				summarySiGui.valueBoundary = result.valueBoundary;
			}

			siEmbeddedEntries.push(new SiEmbeddedEntry(siComp, summarySiGui));
		}

		return siEmbeddedEntries;
	}

	val(siEmbeddedEntries: SiEmbeddedEntry[]): void {
		const request = new SiValRequest();

		siEmbeddedEntries.forEach((siEmbeddedEntry, i) => {
			request.instructions[i] = this.createValInstruction(siEmbeddedEntry);

			// siEmbeddedEntry.entry.resetError();
		});

		this.siService.apiVal(this.siFrame.apiUrl, request).subscribe((response: SiValResponse) => {
			siEmbeddedEntries.forEach((siEmbeddedEntry, i) => {
				this.handleValResult(siEmbeddedEntry, response.results[i]);
			});
		});
	}

	private handleValResult(siEmbeddedEntry: SiEmbeddedEntry, siValResult: SiValResult): void {
		siEmbeddedEntry.valueBoundary.replace(siValResult.getResults[0].valueBoundary!);

		if (siEmbeddedEntry.summaryComp) {
			siEmbeddedEntry.summaryComp.valueBoundary = siValResult.getResults[1].valueBoundary;
		}
	}

	private createValInstruction(siEmbeddedEntry: SiEmbeddedEntry): SiValInstruction {
		const instruction = new SiValInstruction(siEmbeddedEntry.valueBoundary.readInput());

		instruction.getInstructions[0] = SiValGetInstruction.create();

		if (siEmbeddedEntry.summaryComp) {
			siEmbeddedEntry.summaryComp.valueBoundary = null;
			instruction.getInstructions[1] = SiValGetInstruction.create();
		}

		return instruction;
	}
}
