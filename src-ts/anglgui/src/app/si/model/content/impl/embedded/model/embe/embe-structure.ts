import { Embe } from './embe';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { EmbeOutCollection } from './embe-collection';
import { Subscription, Subject, Observable } from 'rxjs';


export class EmbeStructure {
	constructor(readonly embe: Embe, readonly uiStructure: UiStructure) {

	}

	dispose() {
		this.uiStructure.dispose();
	}
}


export class EmbeStructureCollection {
	public embeStructures = new Array<EmbeStructure>();
	private subscription = new Subscription();
	private reducedErrorsChangedSubject = new Subject<void>();

	constructor(readonly reduced: boolean, private parentUiStructure: UiStructure, readonly embeCol: EmbeOutCollection) {
	}

	clear() {
		let embeStructure: EmbeStructure;
		while (embeStructure = this.embeStructures.pop()) {
			embeStructure.dispose();
		}
	}

	private splieEmbeStrucutre(embe: Embe): EmbeStructure|null {
		const i = this.embeStructures.findIndex(es => es.embe === embe);
		if (i === -1) {
			return null;
		}

		return this.embeStructures.splice(i, 1)[0];
	}

	refresh() {
		const embeStructures = new Array<EmbeStructure>();

		this.subscription.unsubscribe();
		this.subscription = new Subscription();

		for (const embe of this.embeCol.embes) {
			let embeStructure = this.splieEmbeStrucutre(embe);
			if (!embeStructure) {
				embeStructure = new EmbeStructure(embe, this.parentUiStructure.createChild());
			}

			if (!this.reduced) {
				embeStructure.uiStructure.model = embe.uiStructureModel;
			} else {
				embeStructure.uiStructure.model = embe.summaryUiStructureModel;
				this.subscription.add(embe.uiStructureModel.getStructureErrors$().subscribe(() => {
					this.reducedErrorsChangedSubject.next();
				}));
			}

			embeStructures.push(embeStructure);
		}

		this.clear();
		this.embeStructures = embeStructures;
	}

	get reducedErrorsChanged$(): Observable<void> {
		return this.reducedErrorsChangedSubject.asObservable();
	}

}
