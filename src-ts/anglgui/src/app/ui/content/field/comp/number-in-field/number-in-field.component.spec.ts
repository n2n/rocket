import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { NumberInFieldComponent } from './number-in-field.component';

describe('NumberInFieldComponent', () => {
	let component: NumberInFieldComponent;
	let fixture: ComponentFixture<NumberInFieldComponent>;

	beforeEach(async(() => {
		TestBed.configureTestingModule({
			declarations: [ NumberInFieldComponent ]
		})
		.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(NumberInFieldComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
		expect(component).toBeTruthy();
	});
});
