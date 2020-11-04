import { Component, OnInit, Input, EventEmitter, Output } from '@angular/core';
import { SiPageCollection } from '../../model/si-page-collection';
import { Subscription } from 'rxjs';

@Component({
	selector: 'rocket-si-pagination.rocket-pagination',
	templateUrl: './pagination.component.html',
	styleUrls: ['./pagination.component.css']
})
export class PaginationComponent implements OnInit {

	private _internalValue = 1;

	@Input()
	siPageCollection: SiPageCollection;

	private subscription: Subscription;

	constructor() { }

	ngOnInit() {
		this._internalValue = this.siPageCollection.currentPageNo;
		this.subscription = this.siPageCollection.currentPageNo$
				.subscribe((currentPageNo) => {
					this._internalValue = currentPageNo;
				});
	}

	ngOnDestory() {
		this.subscription.unsubscribe();
	}

	get visible(): boolean {
		return this.lastPageNo > 1;
	}

	get internalValue(): number {
		return this._internalValue;
	}

	set internalValue(currentPageNo: number) {
		if (this._internalValue === currentPageNo || 1 > currentPageNo || this.lastPageNo < currentPageNo) {
			return;
		}

		this._internalValue = currentPageNo;
		this.siPageCollection.currentPageNo = currentPageNo;
	}

	get lastPageNo(): number {
		return this.siPageCollection.pagesNum;
	}


}
