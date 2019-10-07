import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CrumbGroupComponent } from './crumb-group.component';

describe('CrumbGroupComponent', () => {
	let component: CrumbGroupComponent;
	let fixture: ComponentFixture<CrumbGroupComponent>;

	beforeEach(async(() => {
	TestBed.configureTestingModule({
		declarations: [ CrumbGroupComponent ]
	})
	.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(CrumbGroupComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
	expect(component).toBeTruthy();
	});
});
