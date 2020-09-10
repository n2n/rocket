import { TestBed } from '@angular/core/testing';

import { UserDaoService } from './user-dao.service';

describe('UserDaoService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: UserDaoService = TestBed.get(UserDaoService);
    expect(service).toBeTruthy();
  });
});
