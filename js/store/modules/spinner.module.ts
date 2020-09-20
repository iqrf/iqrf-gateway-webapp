import {GetterTree, MutationTree} from 'vuex';

const state = {
	enabled: null,
	text: null,
};

const getters: GetterTree<any, any> = {
	isEnabled(state): boolean {
		return state.enabled;
	},
	text(state): string | null {
		return state.text;
	},
};

const mutations: MutationTree<any> = {
	HIDE(state) {
		state.enabled = false;
		state.text = null;
	},
	SHOW(state, text: string | null = null) {
		state.enabled = true;
		state.text = text;
	},
	UPDATE_TEXT(state, text: string | null) {
		state.text = text;
	},
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
};
