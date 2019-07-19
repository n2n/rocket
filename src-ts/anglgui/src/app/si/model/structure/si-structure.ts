
import { SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";
import { Subject, Observable, BehaviorSubject } from "rxjs";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { SiZoneError } from "src/app/si/model/structure/si-zone-error";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiStructureModel } from "src/app/si/model/structure/si-structure-model";

export class SiStructure {
	label: string|null = null;
	type: SiStructureType|null = null;
	private visibleSubject = new BehaviorSubject<boolean>(true);
	model: SiStructureModel|null;
	
	constructor() {
	}
	
	get visible(): boolean {
		return this.visibleSubject.getValue();
	}
	
	set visible(visible: boolean) {
		this.visibleSubject.next(visible);
	}
	
	get visible$(): Observable<boolean> {
		return this.visibleSubject;
	}
	
	getZoneErrors(): SiZoneError[] {
		const errors: SiZoneError[] = [];
		
		if (this.model) {
			errors.push(...this.model.getZoneErrors());
		
			for (const child of this.model.getChildren()) {
				errors.push(...child.getZoneErrors());
			}
		}
		
		return errors;
	}
}