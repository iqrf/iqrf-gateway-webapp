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
