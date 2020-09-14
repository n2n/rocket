import { Component, OnInit } from '@angular/core';
import { UserDaoService } from '../../model/user-dao.service';
import { User } from '../../bo/user';
import { AppStateService } from 'src/app/app-state.service';

@Component({
	selector: 'rocket-users-component',
	templateUrl: './users-component.component.html',
	styleUrls: ['./users-component.component.css']
})
export class UsersComponentComponent implements OnInit {

	users: User[]|null = null;

	constructor(private userDao: UserDaoService, private appState: AppStateService) { }

	ngOnInit() {
		this.userDao.getUsers().subscribe(users => {
			this.users = users;
		});
	}
}
