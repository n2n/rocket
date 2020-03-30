import { ClipboardService } from 'src/app/si/model/generic/clipboard.service';
import { SiMaskQualifier } from 'src/app/si/model/meta/si-mask-qualifier';
import { SiEntryQualifier } from '../../../../si-qualifier';
import { SiGenericEmbeddedEntry } from '../../model/generic-embedded';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { Subject } from 'rxjs';

export class ChoosePasteModel {
	addables: SiMaskQualifier[] = [];
	pastables: SiEntryQualifier[] = [];
	illegalPastables: SiEntryQualifier[] = [];

	readonly done$ = new Subject<SiEmbeddedEntry>();

	private siGenericEmbeddedEntries: SiGenericEmbeddedEntry[]|null = null;

	constructor(public siEmbeddedEntry: SiEmbeddedEntry, private clipboardService: ClipboardService) {
		this.update();
	}

	update() {
		this.addables = this.siEmbeddedEntry.maskQualifiers;

		this.pastables = [];
		this.illegalPastables = [];

		this.siGenericEmbeddedEntries = this.clipboardService.filter(SiGenericEmbeddedEntry);
		for (const siGenericEmbeddedEntry of this.siGenericEmbeddedEntries) {
			if (!siGenericEmbeddedEntry.selectedTypeId) {
				continue;
			}

			if (this.siEmbeddedEntry.containsTypeId(siGenericEmbeddedEntry.selectedTypeId)) {
				this.pastables.push(siGenericEmbeddedEntry.entryQualifier);
			} else {
				this.illegalPastables.push(siGenericEmbeddedEntry.entryQualifier);
			}
		}
	}

	chooseAddable(siMaskQualifier: SiMaskQualifier) {
		this.siEmbeddedEntry.selectedTypeId = siMaskQualifier.identifier.typeId;
		this.done$.next(this.siEmbeddedEntry);
		this.done$.complete();
	}

	choosePastable(siEntryQualifier: SiEntryQualifier) {
		const siGenericEmbeddedEntry = this.clipboardService.filter(SiGenericEmbeddedEntry)
				.find((gene) => {
					return gene.entryQualifier.equals(siEntryQualifier);
				});

		if (!siGenericEmbeddedEntry) {
			return;
		}

		this.siEmbeddedEntry.paste(siGenericEmbeddedEntry);
		this.done$.next(this.siEmbeddedEntry);
	}
}
