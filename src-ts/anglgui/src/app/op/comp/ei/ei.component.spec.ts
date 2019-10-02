import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EiComponent } from './ei.component';

describe('EiComponent', () => {
	let component: EiComponent;
	let fixture: ComponentFixture<EiComponent>;

	beforeEach(async(() => {
		TestBed.configureTestingModule({
			declarations: [ EiComponent ]
		})
		.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(EiComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
		expect(component).toBeTruthy();
	});
});
