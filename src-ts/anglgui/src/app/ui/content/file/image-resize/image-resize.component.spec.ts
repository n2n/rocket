import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ImageResizeComponent } from './image-resize.component';

describe('ImageResizeComponent', () => {
  let component: ImageResizeComponent;
  let fixture: ComponentFixture<ImageResizeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ImageResizeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ImageResizeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
