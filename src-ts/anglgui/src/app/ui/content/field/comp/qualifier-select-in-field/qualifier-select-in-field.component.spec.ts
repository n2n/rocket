import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { QualifierSelectInFieldComponent } from './qualifier-select-in-field.component';

describe('QualifierSelectInFieldComponent', () => {
	let component: QualifierSelectInFieldComponent;
	let fixture: ComponentFixture<QualifierSelectInFieldComponent>;

	beforeEach(async(() => {
		TestBed.configureTestingModule({
			declarations: [ QualifierSelectInFieldComponent ]
		})
		.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(QualifierSelectInFieldComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
		expect(component).toBeTruthy();
	});
});
