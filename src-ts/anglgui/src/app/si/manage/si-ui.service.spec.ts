import { TestBed } from '@angular/core/testing';

import { SiUiService } from './si-ui.service';

describe('SiUiService', () => {
	beforeEach(() => TestBed.configureTestingModule({}));

	it('should be created', () => {
		const service: SiUiService = TestBed.get(SiUiService);
		expect(service).toBeTruthy();
	});
});
