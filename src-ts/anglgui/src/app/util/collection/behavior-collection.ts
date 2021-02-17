import { BehaviorSubject, Observable } from 'rxjs';

export class BehaviorCollection<T> {

	private subject: BehaviorSubject<T[]>;

	constructor(values: T[] = []) {
		this.subject = new BehaviorSubject<T[]>(values);
	}

	clear(): BehaviorCollection<T> {
		this.subject.next([]);
		return this;
	}

	push(...ts: T[]): BehaviorCollection<T> {
		const arr = this.subject.getValue();
		arr.push(...ts);
		this.subject.next(arr);
		return this;
	}

	get$(): Observable<T[]> {
		return this.subject.asObservable();
	}

	get(): T[] {
		return this.subject.getValue();
	}

	set(ts: T[]) {
		this.subject.next(ts);
	}

	dispose(): void {
		this.subject.complete();
		this.subject = null;
	}

}