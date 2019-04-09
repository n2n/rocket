import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'rocket-ui-pagination',
  templateUrl: './pagination.component.html',
  styleUrls: ['./pagination.component.css']
})
export class PaginationComponent implements OnInit {

	@Input() curPageNo: number
	@Input() lastPageNo: number;
 
	constructor() { }

	ngOnInit() {
	}
}
