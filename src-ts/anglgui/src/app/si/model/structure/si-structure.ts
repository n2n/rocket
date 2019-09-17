
import { SiStructureType } from 'src/app/si/model/entity/si-field-structure-declaration';
import { Observable, BehaviorSubject } from 'rxjs';
import { SiZoneError } from 'src/app/si/model/structure/si-zone-error';
import { SiStructureModel } from 'src/app/si/model/structure/si-structure-model';
import { SiControl } from 'src/app/si/model/control/si-control';
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

export class SiStructure {
    private children: SiStructure[] = [];
	private visibleSubject = new BehaviorSubject<boolean>(true);
	controls: SiControl[] = [];
	
	private disposed = false;

	constructor(readonly parent: SiStructure|null, public type: SiStructureType|null = null, 
	        public label: string|null = null, private _model: SiStructureModel|null = null) {
	    if (parent) {
	        parent.registerChild(this);
	    }
	}
	
	private ensureNotDisposed() {
	    if (!this.disposed) {
	        return;
	    }
	    
	    throw new IllegalSiStateError('SiStructure already disposed.');
	}
	
	get model(): SiStructureModel|null {
		this.ensureNotDisposed();
		return this._model;
	}
	
	set model(model: SiStructureModel|null) {
		this.ensureNotDisposed();
		
	    if (this._model === model) {
	        return;
	    }
	    
	    this.clear();
	    this._model = model;
	}
		
	private clear() {
        for (const child of [...this.children]) {
            child.dispose();
        }
        
        if (this.children.length !== 0) {
            throw new IllegalSiStateError('Leftover children!')
        }
	}

	dispose() {
		if (this.disposed) {
			return;
		}
		
		this.disposed = true;
		
        this.clear();
	    
        if (this.parent) {
            this.parent.unregisterChild(this);
        }
	}
	
	protected registerChild(child: SiStructure) {
		this.ensureNotDisposed();
		
	    const i = this.children.indexOf(child);
	    if (i !== -1 || this === child) {
	        throw new IllegalSiStateError('Child already exists or is same as parent.');
	    }
	    
	    this.children.push(child)
	}
	
	protected unregisterChild(child: SiStructure) {
	    const i = this.children.indexOf(child);
        if (i === -1) {
            throw new IllegalSiStateError('Unknown child.');
        }
	    
        this.children.splice(i, 1);
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
		this.ensureNotDisposed();
		
		const errors: SiZoneError[] = [];

		if (this.model) {
			errors.push(...this.model.getZoneErrors());
		}
		
		for (const child of this.children) {
            errors.push(...child.getZoneErrors());
        }

		return errors;
	}
}
