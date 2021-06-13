import { Component, OnInit } from '@angular/core';
import { ToolsService } from '../../model/tools.service';
import { MailItem } from '../../bo/mail-item';
import {animate, state, style, transition, trigger} from '@angular/animations';
import {LogFileData} from '../../bo/log-file-data';

@Component({
selector: 'rocket-mail-center',
templateUrl: './mail-center.component.html',
styleUrls: ['./mail-center.component.css'],
	animations: [
	trigger('slide', [
		state('open', style({
		display: 'block'
		})),
		state('closed', style({
		display: 'none'
		})),
		transition('open => closed', [
		animate('0s')
		]),
		transition('closed => open', [
		animate('0s')
		]),
	]),
	]
})
export class MailCenterComponent implements OnInit {

	public mailItems: MailItem[]|null = null;
	public currentLogFileData: LogFileData = new LogFileData(null, 0);
	public mailLogFileDatas: LogFileData[] = [];
	private pCurrentPageNo = 1;

	constructor(private toolsService: ToolsService) { }

	ngOnInit(): void {
		this.toolsService.getMailLogFileDatas().toPromise().then((logFileDatas) => {
			this.mailLogFileDatas = logFileDatas;
			if (this.mailLogFileDatas[0]) {
				this.currentLogFileData = this.mailLogFileDatas[0];
				this.updateMailItems();
			} else {
				this.currentLogFileData = null;
				this.mailItems = [];
			}
		});
	}

	private updateMailItems(): void {
		this.mailItems = null;
		this.toolsService.getMails(this.currentLogFileData, this.pCurrentPageNo).toPromise().then((mailItems: MailItem[]) => {
			this.mailItems = mailItems;
		});
	}

	mailLogFileChanged(logFileDate: LogFileData): void {
		this.currentLogFileData = logFileDate;
		this.updateMailItems();
	}

	set currentPageNo(pageNo: number) {
		if (this.pCurrentPageNo === pageNo || this.pCurrentPageNo > this.currentLogFileData.numPages) {
			return;
		}
		this.currentPageNo = pageNo;
		this.updateMailItems();
	}

	get currentPageNo(): number {
		return this.pCurrentPageNo;
	}

	prettifyFilename(filename: string): string {
	let prettyName = filename.split('-').join(' ');
	prettyName = prettyName.replace('.xml', '');
	return prettyName;
	}
}
