import { Component, OnInit } from '@angular/core';
import { PasswordInModel } from '../password-in-model';

@Component({
  selector: 'rocket-password-in',
  templateUrl: './password-in.component.html'
})
export class PasswordInComponent implements OnInit {
	model: PasswordInModel;
	type = 'password';
	unblocked = false;

	public pRawPassword: string|null = null;

	constructor() { }

	ngOnInit(): void {

	}

	get rawPassword(): string|null {
		if (this.blocked) {
			return 'holeradio';
		}

		return this.pRawPassword;
	}

	set rawPassword(rawPassword: string|null) {
		if (this.blocked) {
			return;
		}
		if (rawPassword === '') {
			rawPassword = null;
		}
		this.pRawPassword = rawPassword;
		this.model.setRawPassword(rawPassword);
	}

	get blocked(): boolean {
		return this.model.isPasswordSet() && !this.unblocked;
	}

	get passwordVisible(): boolean {
		return this.type === 'text';
	}

	changeType(): void {
		if (this.type === 'password') {
			this.type = 'text';
			return;
		}

		this.type = 'password';
	}

	setUnblocked(unblocked: boolean): void {
		this.unblocked = unblocked;
	}

	applyGeneratedPassword(): void {
		let passwordLength = 12;
		if (passwordLength < this.model.getMinlength()) {
			passwordLength = this.model.getMinlength();
		}

		if (passwordLength > this.model.getMaxlength()) {
			passwordLength = this.model.getMaxlength();
		}

		this.rawPassword = this.generatePassword(passwordLength);
		this.type = 'text';
	}

	private generatePassword(passwordLength: number): string {
		const passwordChars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz#@!%&()/';
		return Array(passwordLength).fill(passwordChars).map((x) => {
			return x[Math.floor(Math.random() * x.length)];
		}).join('');
	}
}
