import { Component, OnInit, Input, EventEmitter, Output } from '@angular/core';

@Component({
  selector: 'rocket-ui-pagination',
  templateUrl: './pagination.component.html',
  styleUrls: ['./pagination.component.css']
})
export class PaginationComponent implements OnInit {

	private _currentPageNo: number
	@Output() currentPageNoChange = new EventEmitter<number>();
	@Input() lastPageNo: number;
 
	constructor() { }

	ngOnInit() {
	}
	
	@Input() 
	set currentPageNo(currentPageNo: number) {
		if (this._currentPageNo == currentPageNo || 1 > currentPageNo || this.lastPageNo < currentPageNo) {
			return;
		}
		
		this._currentPageNo = currentPageNo;
		this.currentPageNoChange.emit(currentPageNo);
	}
	
	get currentPageNo(): number {
		return this._currentPageNo;
	}
}
