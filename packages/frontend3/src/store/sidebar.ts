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

import { defineStore } from 'pinia';

interface SidebarState {
	minimized: boolean;
	visible: boolean;
}

export const useSidebarStore = defineStore('sidebar', {
	state: (): SidebarState => ({
		minimized: false,
		visible: true,
	}),
	actions: {
		/**
		 * Sets sidebar visibility
		 * @param {boolean} visible Sidebar visibility
		 */
		setVisibility(visible: boolean) {
			this.visible = visible;
		},
		/**
		 * Toggles sidebar size
		 */
		toggleSize() {
			this.minimized = !this.minimized;
		},
		/**
		 * Toggles sidebar visibility
		 */
		toggleVisibility() {
			this.visible = !this.visible;
		},
	},
	getters: {
		/**
		 * Is the sidebar minimized?
		 * @returns {boolean} True if the sidebar is minimized, false otherwise
		 */
		isMinimized(): boolean {
			return this.minimized;
		},
		/**
		 * Is the sidebar visible?
		 * @returns {boolean} True if the sidebar is visible, false otherwise
		 */
		isVisible(): boolean {
			return this.visible;
		},
	},
	persist: true,
});
