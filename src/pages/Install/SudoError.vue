<template>
	<CCard>
		<CCardHeader color='danger' class='text-white'>
			{{ $t('install.error.sudo.title') }}
		</CCardHeader>
		<CCardBody>
			<span v-if='!(exists === "true")'>
				{{ $t('install.error.sudo.missing') }}
			</span>
			<br>
			<span v-if='!(userSudo === "true")'>
				{{ $t('install.error.sudo.invalid') }}
			</span>
		</CCardBody>
		<CCardFooter>
			<strong>{{ $t('install.error.howToFix') }}</strong>
			<div v-if='!(exists === "true")'>
				<br>
				{{ $t('install.error.sudo.fixMissing') }}
				<prism-editor
					v-model='fixCommands.exists'
					:highlight='highlighter'
					:readonly='true'
				/>
			</div>
			<div v-if='!(exists === "true")'>
				<br>
				{{ $t('install.error.sudo.fixInvalid') }}
				<prism-editor
					v-model='fixCommands.userSudo'
					:highlight='highlighter'
					:readonly='true'
				/>
			</div>
		</CCardFooter>
	</CCard>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardFooter, CCardHeader} from '@coreui/vue/src';

import {PrismEditor} from 'vue-prism-editor';
import 'vue-prism-editor/dist/prismeditor.min.css';
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-bash';
import 'prismjs/themes/prism.css';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardFooter,
		CCardHeader,
		PrismEditor,
	},
	metaInfo: {
		title: 'install.error.sudo.title'
	},
})

/**
 * Missing sudo notification component
 */
export default class SudoError extends Vue {

	/**
	 * Commands to fix this issue
	 */
	private fixCommands = {
		exists: 'su\napt-get install sudo',
		userSudo: ' ALL=(ALL) NOPASSWD:ALL" | sudo tee -a /etc/sudoers.d/iqrf-gateway-webapp >/dev/null'
	}

	/**
	 * @property {string} user User under which webapp runs
	 */
	@Prop({required: true, default: null}) user!: string

	/**
	 * @property {boolean} exists Does sudo utility exist?
	 */
	@Prop({required: true, default: false}) exists!: boolean

	/**
	 * @property {boolean} userSudo Can webapp executo commands with sudo?
	 */
	@Prop({required: true, default: false}) userSudo!: boolean

	/**
	 * Initializes userSudo command with user name
	 */
	mounted(): void {
		this.fixCommands.userSudo = 'echo "' + this.user + this.fixCommands.userSudo;
	}

	/**
	 * JSON highlighter method
	 * @param code Code to highlight
	 * @return Highlighted code
	 */
	private highlighter(code: string): string {
		return Prism.highlight(code, Prism.languages.bash, 'bash');
	}
}
</script>
