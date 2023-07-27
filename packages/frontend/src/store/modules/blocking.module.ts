/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
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
