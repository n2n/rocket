import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EmbeddedEntriesSummaryOutComponent } from './embedded-entries-summary-out.component';

describe('EmbeddedEntriesSummaryOutComponent', () => {
	let component: EmbeddedEntriesSummaryOutComponent;
	let fixture: ComponentFixture<EmbeddedEntriesSummaryOutComponent>;

	beforeEach(async(() => {
		TestBed.configureTestingModule({
			declarations: [ EmbeddedEntriesSummaryOutComponent ]
		})
		.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(EmbeddedEntriesSummaryOutComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
		expect(component).toBeTruthy();
	});
});
