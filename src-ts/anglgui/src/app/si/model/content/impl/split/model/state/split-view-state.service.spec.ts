import { TestBed } from '@angular/core/testing';

import { SplitViewStateService } from './split-view-state.service';

describe('SplitViewStateService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: SplitViewStateService = TestBed.get(SplitViewStateService);
    expect(service).toBeTruthy();
  });
});
