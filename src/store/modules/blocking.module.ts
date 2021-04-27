import { text } from '@fortawesome/fontawesome-svg-core';
import {GetterTree, MutationTree} from 'vuex';

/**
 * Blocking component state interface
 */
export interface BlockingState {
	/**
	 * Blocking component enabled
	 */
	enabled: boolean
	
	/**
	 * Blocking component text
	 */
	text: string|null
}

const state: BlockingState = {
	enabled: false,
	text: null
};

const getters: GetterTree<BlockingState, any> = {
	isEnabled(state: BlockingState): boolean {
		return state.enabled;
	},
	text(state: BlockingState): string|null {
		return state.text;
	},
};

const mutations: MutationTree<BlockingState> = {
	SHOW(state: BlockingState, text: string|null = null) {
		state.enabled = true;
		state.text = text;
	},
	HIDE(state: BlockingState) {
		state.enabled = false;
		state.text = null;
	},
	UPDATE_TEXT(state: BlockingState, text: string|null) {
		state.text = text;
	},
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
};
