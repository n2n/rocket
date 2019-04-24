import { TestBed } from '@angular/core/testing';

import { SiCommanderService } from './si-commander.service';

describe('SiCommanderService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: SiCommanderService = TestBed.get(SiCommanderService);
    expect(service).toBeTruthy();
  });
});
