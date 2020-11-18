import { Component, OnInit } from '@angular/core';
import {ToolsService} from "../../model/tools.service";
import {MailItem} from "../../bo/mail-item";

@Component({
  selector: 'rocket-mail-center',
  templateUrl: './mail-center.component.html',
  styleUrls: ['./mail-center.component.css']
})
export class MailCenterComponent implements OnInit {

  public mailItems: MailItem[]|null = null;
  public totalPagesNum: number;
  public currentPageNo: number = 1;

  constructor(private _toolsService: ToolsService) { }

  ngOnInit(): void {
    this._toolsService.getPagesCount().toPromise().then((pagesNum: number) => {
      this.totalPagesNum = pagesNum;
    });
    this.updateMailItems();
  }

  private updateMailItems() {
    this.mailItems = null;
    this._toolsService.getMails(this.currentPageNo).toPromise().then((mailItems: MailItem[]) => {
      this.mailItems = mailItems;
    });
  }

  onPageChanged(pageNo: number) {
    alert('page changed');

    if (this.currentPageNo === pageNo || this.currentPageNo > this.totalPagesNum) {
      return;
    }

    this.currentPageNo= pageNo;
    this.updateMailItems();
  }
}
