import { ComponentFixture, TestBed } from '@angular/core/testing';

import { UrlIframeComponent } from './url-iframe.component';

describe('UrlIframeComponentComponent', () => {
	let component: UrlIframeComponent;
	let fixture: ComponentFixture<UrlIframeComponent>;

	beforeEach(async () => {
	await TestBed.configureTestingModule({
		declarations: [ UrlIframeComponent ]
	})
	.compileComponents();
	});

	beforeEach(() => {
	fixture = TestBed.createComponent(UrlIframeComponent);
	component = fixture.componentInstance;
	fixture.detectChanges();
	});

	it('should create', () => {
	expect(component).toBeTruthy();
	});
});
