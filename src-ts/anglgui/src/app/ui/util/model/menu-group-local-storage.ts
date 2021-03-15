import {UiMenuGroup} from '../../structure/model/ui-menu';

export class MenuGroupLocalStorage {
  public static UI_MENU_GROUP_OPEN_STATE_KEY = 'ui-menu-group-open-state'

  private static async readUiMenuGroupOpenStates(): Promise<OpenState[]> {
    let openStatesJsonString = localStorage.getItem(this.UI_MENU_GROUP_OPEN_STATE_KEY);
    if (!!openStatesJsonString) {
      return JSON.parse(openStatesJsonString);
    }

    return [];
  }

  static async saveOpenState(menuGroup: UiMenuGroup, state: boolean) {
    let openStates = await this.readUiMenuGroupOpenStates();

    let item = openStates.find(mg => mg.id == menuGroup.id);
    if (!!item) {
      item.isOpen = state
    } else {
      openStates.push({'id':menuGroup.id, 'isOpen':state});
    }

    localStorage.setItem(this.UI_MENU_GROUP_OPEN_STATE_KEY, JSON.stringify(openStates));
  }

  static async toggleOpenStates(menuGroups: UiMenuGroup[]) {
    let openStates = await this.readUiMenuGroupOpenStates();

    openStates.forEach((openState) => {
      let menuGroup = menuGroups.find(mg => mg.id == openState.id);
      if (!!menuGroup) {
        menuGroup.isOpen = openState.isOpen;
      } else {
        this.removeOpenState(openState);
      }
    });
  }

  private static async removeOpenState(openState: OpenState) {
    let openStates = await this.readUiMenuGroupOpenStates();
    openStates.splice(openStates.indexOf(openStates.find(os => os.id == openState.id), 1));
    localStorage.setItem(this.UI_MENU_GROUP_OPEN_STATE_KEY, JSON.stringify(openStates));
  }
}

export interface OpenState {
  id, isOpen
}
