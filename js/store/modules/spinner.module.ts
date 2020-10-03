import {GetterTree, MutationTree} from 'vuex';

export class SpinnerOptions {
	public text: string | null = null;
	public timeout: number | null = null;
}

const state = {
	enabled: null,
	text: null,
	timeout: null,
};

const actions = {
	// @ts-ignore
	show({commit, state}, options: SpinnerOptions) {
		commit('SHOW', options.text);
		if (options.timeout === null) {
			return;
		}
		state.timeout = setTimeout(() => {
			commit('HIDE');
		}, options.timeout);
	},
	// @ts-ignore
	hide({commit, state}) {
		commit('HIDE');
		clearTimeout(state.timeout);
	}
};

const getters: GetterTree<any, any> = {
	isEnabled(state): boolean {
		return state.enabled;
	},
	text(state): string|null {
		return state.text;
	},
};

const mutations: MutationTree<any> = {
	HIDE(state) {
		state.enabled = false;
		state.text = null;
	},
	SHOW(state, text: string|null = null) {
		state.enabled = true;
		state.text = text;
	},
	UPDATE_TEXT(state, text: string|null) {
		state.text = text;
	},
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
	actions,
};
