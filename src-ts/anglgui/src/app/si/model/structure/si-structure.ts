
import { SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";
import { Subject, Observable, BehaviorSubject } from "rxjs";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";

export class SiStructure {
	label: string|null = null;
	type: SiStructureType|null = null;
	private visibleSubject = new BehaviorSubject<boolean>(true);
	private children: SiStructure[] = [];
	content: SiStructureContent|null = null;
	
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
	
	addChild(child: SiStructure) {
		this.children.push(child);
	}
	
	getChildren(): SiStructure[] {
		return this.children;
	}
	
	clearChildren() {
		this.children.length = 0;
	}
}