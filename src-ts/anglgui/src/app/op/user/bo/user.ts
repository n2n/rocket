
export class User {
	firstname: string;
	lastname: string;
	email: string;

	constructor(public id: number, public username: string, public power: UserPower) {

	}

	get fullname(): string|null {
		if (!this.firstname && !this.lastname) {
			return null;
		}

		return this.firstname + ' ' + this.lastname;
	}
}

export enum UserPower {
	SUPER_ADMIN = 'superadmin',
	ADMIN = 'admin',
	NONE = 'none'
}
