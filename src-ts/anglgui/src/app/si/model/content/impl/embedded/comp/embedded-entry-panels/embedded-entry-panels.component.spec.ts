import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EmbeddedEntryPanelsComponent } from './embedded-entries-panels-in.component';

describe('EmbeddedEntryPanelsComponent', () => {
	let component: EmbeddedEntryPanelsComponent;
	let fixture: ComponentFixture<EmbeddedEntryPanelsComponent>;

	beforeEach(async(() => {
		TestBed.configureTestingModule({
			declarations: [ EmbeddedEntryPanelsComponent ]
		})
		.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(EmbeddedEntryPanelsComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
		expect(component).toBeTruthy();
	});
});
