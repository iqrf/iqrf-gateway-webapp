/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
	show: boolean|string;

	/**
	 * Is the sidebar minimized?
	 */
	minimize: boolean;

}

const state: SidebarState = {
	show: 'responsive',
	minimize: false,
};

const getters: GetterTree<SidebarState, any> = {
	isMinimized(state: SidebarState): boolean {
		return state.minimize;
	},
};

const mutations: MutationTree<SidebarState> = {
	toggleSidebarDesktop (state: SidebarState) {
		const sidebarOpened = [true, 'responsive'].includes(state.show);
		state.show = sidebarOpened ? false : 'responsive';
	},
	toggleSidebarMobile (state: SidebarState) {
		const sidebarClosed = [false, 'responsive'].includes(state.show);
		state.show = sidebarClosed ? true : 'responsive';
	},
	set (state: SidebarState, [variable, value]) {
		state[variable] = value;
	}
};

export default {
	namespaced: true,
	state,
	getters,
	mutations
};
