import { Component, OnInit } from '@angular/core';
import { UserDaoService } from '../../model/user-dao.service';
import { ActivatedRoute, Router } from '@angular/router';
import { User } from '../../bo/user';
import { TranslationService } from 'src/app/util/i18n/translation.service';

@Component({
	selector: 'rocket-user',
	templateUrl: './user.component.html',
	styleUrls: ['./user.component.css']
})
export class UserComponent implements OnInit {
	user: User|null = null;
	saving = false;

	constructor(private userDao: UserDaoService, private route: ActivatedRoute, private translationService: TranslationService,
			private router: Router) {
	}

	ngOnInit() {
		this.route.params.subscribe((params) => {
			this.userDao.getUserById(params.userId).subscribe((user: User) => {
				this.user = user;
			});
		});
	}

	get title(): string {
		return this.user ? this.user.username : this.translationService.translate('edit_user_txt');
	}

	save() {
		if (this.saving) {
			return;
		}

		this.saving = true;
		this.userDao.saveUser(this.user).subscribe(() => {
            
		});
	}

	cancel() {
		if (!this.saving) {
			return;
		}

		this.router.navigate(['users']);
	}

}
