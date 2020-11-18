import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {SiPageCollection} from "../../../../si/model/gui/impl/model/si-page-collection";
import {Subscription} from "rxjs";

@Component({
  selector: 'rocket-ui-pagination',
  templateUrl: './pagination.component.html',
  styleUrls: ['./pagination.component.css']
})
export class PaginationComponent implements OnInit {

  @Input() totalPagesNum: number;
  @Input() currentPageNum = 1;
  @Output() currentPageNumChange = new EventEmitter<number>();

  set internalPageNum(pageNum) {
    if (this.currentPageNum === pageNum) {
      return;
    }

    this.currentPageNum = pageNum;
    this.currentPageNumChange.emit(pageNum);
  }

  get internalPageNum(): number {
    return this.currentPageNum;
  }

  get lastPageNum(): number {
    return this.totalPagesNum;
  }

  ngOnInit(): void {
  }
}
