import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {MailItem} from '../bo/mail-item';
import {MailItemFactory} from '../build/mail-item-factory';

@Injectable({
	providedIn: 'root'
})
export class ToolsService {
	constructor(private httpClient: HttpClient) {
	}

	getPagesCount(): Observable<number> {
		return this.httpClient.get<any>('tools/mail-center/mailspagecount')
			.pipe(map((data) => {
				return data;
			}));
	}

	getMails(pageNum: number): Observable<MailItem[]> {
		return this.httpClient.get<any>('tools/mail-center/mails/' + pageNum)
			.pipe(map((data) => {
				return MailItemFactory.createMailItems(data);
			}));
	}
}
