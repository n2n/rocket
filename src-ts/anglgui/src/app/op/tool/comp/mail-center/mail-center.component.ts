import { Component, OnInit } from '@angular/core';
import { ToolsService } from '../../model/tools.service';
import { MailItem } from '../../bo/mail-item';
import {MailItemAttachment} from "../../bo/mail-item-attachment";

@Component({
selector: 'rocket-mail-center',
templateUrl: './mail-center.component.html',
styleUrls: ['./mail-center.component.css']
})
export class MailCenterComponent implements OnInit {

	public mailItems: MailItem[]|null = null;
	public totalPagesNo: number;
	private _currentPageNo = 1;

	constructor(private _toolsService: ToolsService) { }

	ngOnInit(): void {
		this._toolsService.getPagesCount().toPromise().then((totalPagesNo: number) => {
			this.totalPagesNo = totalPagesNo;
		});
		this.updateMailItems();
	}

	private updateMailItems() {
		this.mailItems = null;
		this._toolsService.getMails(this._currentPageNo).toPromise().then((mailItems: MailItem[]) => {
			this.mailItems = mailItems;
		});
	}

	set currentPageNo(pageNo: number) {
		if (this._currentPageNo === pageNo || this._currentPageNo > this.totalPagesNo) {
			return;
		}
		this._currentPageNo = pageNo;
		this.updateMailItems();
	}

	get currentPageNo(): number {
		return this._currentPageNo;
	}
}
