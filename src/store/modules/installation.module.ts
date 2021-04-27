import {GetterTree, MutationTree} from 'vuex';

export interface InstallationState {

	/**
	 * Is installation checked?
	 */
	checked: boolean;

}

const state: InstallationState = {
	checked: false,
};

const getters: GetterTree<InstallationState, any> = {
	isChecked(state: InstallationState): boolean {
		return state.checked;
	},
};

const mutations: MutationTree<InstallationState> = {
	CHECKED(state: InstallationState) {
		state.checked = true;
	}
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
};
