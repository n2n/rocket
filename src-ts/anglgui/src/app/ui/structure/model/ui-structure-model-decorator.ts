// import { UiStructureModel, UiStructureModelMode } from './ui-structure-model';
// import { UiStructure } from './ui-structure';
// import { UiContent } from './ui-content';
// import { Observable } from 'rxjs';
// import { UiStructureError } from './ui-structure-error';
// import { map } from 'rxjs/operators';

// export class UiStructureModelDecorator implements UiStructureModel {
// 	public additionalToolbarStructureModels: UiStructureModel[] = [];

// 	constructor(readonly decorated: UiStructureModel) {
// 	}

// 	bind(uiStructure: UiStructure): void {
// 		this.decorated.bind(uiStructure);
// 	}
// 	unbind(): void {
// 		this.decorated.unbind();
// 	}
// 	getContent(): UiContent|null {
// 		return this.decorated.getContent();
// 	}
// 	getMainControlContents(): UiContent[] {
// 		return this.decorated.getMainControlContents();
// 	}
// 	getAsideContents(): UiContent[] {
// 		return this.decorated.getAsideContents();
// 	}
// 	getToolbarStructureModels$(): Observable<UiStructureModel[]> {
// 		return this.decorated.getToolbarStructureModels$().pipe(map((structureModels) => {
// 			return [...structureModels, ...this.additionalToolbarStructureModels];
// 		}));
// 	}
// 	getStructureErrors$(): Observable<UiStructureError[]> {
// 		return this.decorated.getStructureErrors$();
// 	}
// 	getStructures$(): Observable<UiStructure[]> {
// 		return this.decorated.getStructures$();
// 	}
// 	getDisabled$(): Observable<boolean> {
// 		return this.decorated.getDisabled$();
// 	}
// 	getMode(): UiStructureModelMode {
// 		return this.decorated.getMode();
// 	}
// }
