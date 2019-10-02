import { TestBed } from '@angular/core/testing';

import { SiService } from './si.service';

describe('SiService', () => {
	beforeEach(() => TestBed.configureTestingModule({}));

	it('should be created', () => {
		const service: SiService = TestBed.get(SiService);
		expect(service).toBeTruthy();
	});
});
