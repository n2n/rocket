import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TextareaInFieldComponent } from './textarea-in-field.component';

describe('TextareaInFieldComponent', () => {
	let component: TextareaInFieldComponent;
	let fixture: ComponentFixture<TextareaInFieldComponent>;

	beforeEach(async(() => {
		TestBed.configureTestingModule({
			declarations: [ TextareaInFieldComponent ]
		})
		.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(TextareaInFieldComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
		expect(component).toBeTruthy();
	});
});
