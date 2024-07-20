
import { SiValueBoundary } from 'src/app/si/model/content/si-value-boundary';
import { BulkyEntrySiGui } from 'src/app/si/model/gui/impl/model/bulky-entry-si-gui';
import { CompactEntrySiGui } from 'src/app/si/model/gui/impl/model/compact-entry-si-gui';
import { SiMaskQualifier } from 'src/app/si/model/meta/si-mask-qualifier';
import { SiGenericEmbeddedEntry } from './generic/generic-embedded';
import { SiInputResetPoint } from '../../../si-input-reset-point';

export class SiEmbeddedEntry {

	constructor(public comp: BulkyEntrySiGui, public summaryComp: CompactEntrySiGui|null) {
	}

	get summaryEntry(): SiValueBoundary|null {
		if (this.summaryComp) {
			return this.summaryComp.valueBoundary;
		}

		return null;
	}

	get maskQualifier(): SiMaskQualifier {
		return this.comp.getBoundDeclaration().getMaskById(this.valueBoundary.selectedEntry.getMaskId()).qualifier;
	}

	get valueBoundary(): SiValueBoundary {
		return this.comp.valueBoundary!;
	}

	set valueBoundary(entry: SiValueBoundary) {
		this.comp.valueBoundary = entry;
	}

	async copy(): Promise<SiGenericEmbeddedEntry> {
		return new SiGenericEmbeddedEntry(await this.comp.valueBoundary!.copy(),
				(this.summaryComp ? await this.summaryComp.valueBoundary!.copy() : null));
	}

	async paste(genericEmbeddedEntry: SiGenericEmbeddedEntry): Promise<boolean> {
		const promise = this.comp.valueBoundary!.paste(genericEmbeddedEntry.genericValueBoundary);
		if (!await promise) {
			return false;
		}

		// if (this.summaryComp && genericEmbeddedEntry.summaryGenericEntry) { {

		// }

		// if (this.summaryComp && genericEmbeddedEntry.summaryGenericEntry) {
		// 	return await Promise.all([promise, this.summaryComp.entry.paste(genericEmbeddedEntry.summaryGenericEntry)])
		// 			.then((values) => { return -1 === values.indexOf(true)});
		// }

		// todo
		// validate and refresh summaryComp 

		return await promise;
	}

	async createResetPoint(): Promise<SiInputResetPoint> {
		return this.comp.valueBoundary!.createInputResetPoint();
	}

	get maskQualifiers(): SiMaskQualifier[] {
		return this.comp.getBoundDeclaration().getMaskQualifiersByIds(this.valueBoundary.maskIds);
	}

	get selectedMaskId(): string|null {
		return this.valueBoundary.selectedMaskId;
	}

	set selectedMaskId(maskId: string|null) {
		this.comp.valueBoundary!.selectedMaskId = maskId;
		if (this.summaryComp && this.summaryComp.valueBoundary) {
			this.summaryComp.valueBoundary.selectedMaskId = maskId;
		}
	}

	containsMaskId(maskId: string): boolean {
		return this.valueBoundary.containsMaskId(maskId);
	}

}

