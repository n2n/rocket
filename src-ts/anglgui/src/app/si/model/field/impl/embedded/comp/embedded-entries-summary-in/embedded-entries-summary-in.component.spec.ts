import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EmbeddedEntriesSummaryInComponent } from './embedded-entries-summary-in.component';

describe('EmbeddedEntriesSummaryInComponent', () => {
	let component: EmbeddedEntriesSummaryInComponent;
	let fixture: ComponentFixture<EmbeddedEntriesSummaryInComponent>;

	beforeEach(async(() => {
	TestBed.configureTestingModule({
		declarations: [ EmbeddedEntriesSummaryInComponent ]
	})
	.compileComponents();
	}));

	beforeEach(() => {
	fixture = TestBed.createComponent(EmbeddedEntriesSummaryInComponent);
	component = fixture.componentInstance;
	fixture.detectChanges();
	});

	it('should create', () => {
	expect(component).toBeTruthy();
	});
});
