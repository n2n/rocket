
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

	isAdmin(): boolean {
		return this.power === UserPower.SUPER_ADMIN || this.power === UserPower.ADMIN;
	}

	isSuperAdmin(): boolean {
		return this.power === UserPower.SUPER_ADMIN;
	}

	isEditableBy(user: User): boolean {
		return this.equals(user) || user.isSuperAdmin()
				|| (user.isAdmin() && !this.isSuperAdmin());
	}

	equals(o: object): boolean {
		return o instanceof User && (o as User).id === this.id;
	}
}

export enum UserPower {
	SUPER_ADMIN = 'superadmin',
	ADMIN = 'admin',
	NONE = 'none'
}
