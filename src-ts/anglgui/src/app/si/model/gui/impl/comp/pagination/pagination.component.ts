import { Component, OnInit } from '@angular/core';
import { CompactExplorerComponent } from '../compact-explorer/compact-explorer.component';

@Component({
	selector: 'rocket-si-pagination.rocket-pagination',
	templateUrl: './pagination.component.html',
	styleUrls: ['./pagination.component.css']
})
export class PaginationComponent implements OnInit {

	public cec: CompactExplorerComponent;

	constructor() { }

	ngOnInit(): void {
	}

	get visible(): boolean {
		return this.cec && this.pagesNum > 1;
	}

	get pagesNum(): number {
		return this.cec.pagesNum;
	}

	get currentPageNo(): number {
		return this.cec.currentPageNo;
	}

	set currentPageNo(pageNo: number) {
		this.cec.currentPageNo = pageNo;
	}
}
