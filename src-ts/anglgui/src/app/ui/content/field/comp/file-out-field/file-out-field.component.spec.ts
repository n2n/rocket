import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { FileOutFieldComponent } from './file-out-field.component';

describe('FileOutFieldComponent', () => {
	let component: FileOutFieldComponent;
	let fixture: ComponentFixture<FileOutFieldComponent>;

	beforeEach(async(() => {
		TestBed.configureTestingModule({
			declarations: [ FileOutFieldComponent ]
		})
		.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(FileOutFieldComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
		expect(component).toBeTruthy();
	});
});
