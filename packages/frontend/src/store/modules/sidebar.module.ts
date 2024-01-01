/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
 * Sidebar state
 */
interface SidebarState {

	/**
	 * Sidebar show state
	 */
	show: boolean;

	/**
	 * Is the sidebar minimized?
	 */
	minimize: boolean;

}

const state: SidebarState = {
	show: true,
	minimize: false,
};

const getters: GetterTree<SidebarState, any> = {
	isMinimized(state: SidebarState): boolean {
		return state.minimize;
	},
	isVisible(state: SidebarState): boolean {
		return state.show;
	},
};

const mutations: MutationTree<SidebarState> = {
	setVisibility(state: SidebarState, show: boolean): void {
		state.show = show;
	},
	toggleVisibility(state: SidebarState): void {
		state.show = !state.show;
	},
	toggleSize(state: SidebarState): void {
		state.minimize = !state.minimize;
	},
};

export default {
	namespaced: true,
	state,
	getters,
	mutations
};
