
import { SiStructureType } from 'src/app/si/model/structure/si-field-structure-declaration';
import { Observable, BehaviorSubject } from 'rxjs';
import { SiZoneError } from 'src/app/si/model/structure/si-zone-error';
import { SiStructureModel } from 'src/app/si/model/structure/si-structure-model';
import { SiControl } from 'src/app/si/model/control/si-control';

export class SiStructure {
	private visibleSubject = new BehaviorSubject<boolean>(true);
	controls: SiControl[] = [];

	constructor(public type: SiStructureType|null = null, public label: string|null = null,
			public model: SiStructureModel|null = null) {
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
