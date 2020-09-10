import { Injectable } from '@angular/core';
import { User } from '../bo/user';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';
import { Extractor } from 'src/app/util/mapping/extractor';
import { UserFactory } from './user-fatory';
import { Observable } from 'rxjs';

@Injectable({
	providedIn: 'root'
})
export class UserDaoService {

	constructor(private httpClient: HttpClient) {
	}

	getUsers(): Observable<User[]> {
		return this.httpClient.get<any>('users')
				.pipe(map((data) => {
					const extr = new Extractor(data);
					return UserFactory.createUsers(extr.reqArray('users'));
				}));
	}
}
