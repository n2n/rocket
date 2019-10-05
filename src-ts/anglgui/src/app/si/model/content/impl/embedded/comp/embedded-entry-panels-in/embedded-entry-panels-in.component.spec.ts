import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EmbeddedEntryPanelsInComponent } from './embedded-entry-panels-in.component';

describe('EmbeddedEntryPanelsInComponent', () => {
	let component: EmbeddedEntryPanelsInComponent;
	let fixture: ComponentFixture<EmbeddedEntryPanelsInComponent>;

	beforeEach(async(() => {
		TestBed.configureTestingModule({
			declarations: [ EmbeddedEntryPanelsInComponent ]
		})
		.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(EmbeddedEntryPanelsInComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
		expect(component).toBeTruthy();
	});
});
