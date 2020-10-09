import {ActionTree, GetterTree, MutationTree} from 'vuex';

export class SpinnerOptions {
	public text: string | null = null;
	public timeout: number | null = null;
}

/**
 * Spinner state
 */
export interface SpinnerState {

	/**
	 * Spinner enablement
	 */
	enabled: boolean;

	/**
	 * Spinner text
	 */
	text: string|null;

	/**
	 * Spinner timeout
	 */
	timeout: number|null;

}

const state: SpinnerState = {
	enabled: false,
	text: null,
	timeout: null,
};

const actions: ActionTree<SpinnerState, any> = {
	show({commit, state}, options: SpinnerOptions) {
		commit('SHOW', options.text);
		if (options.timeout === null) {
			return;
		}
		state.timeout = window.setTimeout(() => {
			commit('HIDE');
		}, options.timeout);
	},
	hide({commit, state}) {
		commit('HIDE');
		if (state.timeout !== null) {
			window.clearTimeout(state.timeout);
		}
	}
};

const getters: GetterTree<SpinnerState, any> = {
	isEnabled(state: SpinnerState): boolean {
		return state.enabled;
	},
	text(state: SpinnerState): string|null {
		return state.text;
	},
};

const mutations: MutationTree<SpinnerState> = {
	HIDE(state: SpinnerState) {
		state.enabled = false;
		state.text = null;
	},
	SHOW(state: SpinnerState, text: string|null = null) {
		state.enabled = true;
		state.text = text;
	},
	UPDATE_TEXT(state: SpinnerState, text: string|null) {
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
