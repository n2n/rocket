import { Component, OnInit, Input, EventEmitter, Output } from '@angular/core';

@Component({
  selector: 'rocket-ui-pagination',
  templateUrl: './pagination.component.html',
  styleUrls: ['./pagination.component.css']
})
export class PaginationComponent implements OnInit {

	private _internalValue: number = 1;
	@Output() currentPageNoChange = new EventEmitter<number>();
	@Input() lastPageNo: number;
 
	constructor() { }

	ngOnInit() {
	}
	
	get internalValue() {
		return this._internalValue;
	}
	
	set internalValue(currentPageNo: number) {
		if (this._internalValue == currentPageNo || 1 > currentPageNo || this.lastPageNo < currentPageNo) {
			return;
		}
		
		this._internalValue = currentPageNo;
		this.currentPageNoChange.emit(currentPageNo);
	}
	
	@Input()
	set currentPageNo(no: number) {
		this._internalValue = no;
	}
}
