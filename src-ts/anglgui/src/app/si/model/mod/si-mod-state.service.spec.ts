import { TestBed } from '@angular/core/testing';

import { SiModStateService } from './si-mod-state.service';

describe('SiModStateService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: SiModStateService = TestBed.get(SiModStateService);
    expect(service).toBeTruthy();
  });
});
