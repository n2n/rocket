import { Component, OnInit } from '@angular/core';
import { ToolsService } from '../../model/tools.service';
import { MailItem } from '../../bo/mail-item';
import {animate, state, style, transition, trigger} from "@angular/animations";

@Component({
selector: 'rocket-mail-center',
templateUrl: './mail-center.component.html',
styleUrls: ['./mail-center.component.css'],
  animations: [
    trigger('slide', [
      state('open', style({
        display: "block"
      })),
      state('closed', style({
        display: "none"
      })),
      transition('open => closed', [
        animate('2.35s')
      ]),
      transition('closed => open', [
        animate('2.35s')
      ]),
    ]),
  ]
})
export class MailCenterComponent implements OnInit {

	public mailItems: MailItem[]|null = null;
	public currentLogFileData: LogFileData = new LogFileData(null, 0);
	public mailLogFileDatas: LogFileData[] = [];
	private _currentPageNo = 1;

	constructor(private _toolsService: ToolsService) { }

	ngOnInit(): void {
		this._toolsService.getMailLogFileDatas().toPromise().then((logFileDatas) => {
			this.mailLogFileDatas = logFileDatas;
			this.currentLogFileData = this.mailLogFileDatas[0];
			this.updateMailItems();
		});
	}

	private updateMailItems() {
		this.mailItems = null;
		this._toolsService.getMails(this.currentLogFileData, this._currentPageNo).toPromise().then((mailItems: MailItem[]) => {
			this.mailItems = mailItems;
		});
	}

	mailLogFileChanged(logFileDate: LogFileData) {
		this.currentLogFileData = logFileDate;
		this.updateMailItems();
	}

	set currentPageNo(pageNo: number) {
		if (this._currentPageNo === pageNo || this._currentPageNo > this.currentLogFileData.numPages) {
			return;
		}
		this._currentPageNo = pageNo;
		this.updateMailItems();
	}

	get currentPageNo(): number {
		return this._currentPageNo;
	}

  prettifyFilename(filename: string): string {
    let prettyName = filename.split("-").join(" ");
    prettyName = prettyName.replace(".xml", "");
    return prettyName;
  }
}

export class LogFileData {
	constructor(public filename: string, public numPages: number) {
	}
}
