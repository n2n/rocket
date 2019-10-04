import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EmbeddedEntriesOutComponent } from './embedded-entries-out.component';

describe('EmbeddedEntriesOutComponent', () => {
	let component: EmbeddedEntriesOutComponent;
	let fixture: ComponentFixture<EmbeddedEntriesOutComponent>;

	beforeEach(async(() => {
		TestBed.configureTestingModule({
			declarations: [ EmbeddedEntriesOutComponent ]
		})
		.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(EmbeddedEntriesOutComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
		expect(component).toBeTruthy();
	});
});
