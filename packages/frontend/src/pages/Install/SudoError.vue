<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<v-card>
		<v-card-title
			color='error'
			class='text-white'
		>
			{{ $t('install.error.sudo.title') }}
		</v-card-title>
		<v-card-text>
			<span v-if='!exists'>
				{{ $t('install.error.sudo.missing') }}
			</span>
			<br>
			<span v-if='!userSudo'>
				{{ $t('install.error.sudo.invalid') }}
			</span>
			<v-divider class='my-2' />
			<strong>{{ $t('install.error.howToFix') }}</strong>
			<div v-if='!exists'>
				<br>
				{{ $t('install.error.sudo.fixMissing') }}
				<prism-editor
					v-model='fixCommands.exists'
					:highlight='highlighter'
					:readonly='true'
				/>
			</div>
			<div v-if='!userSudo'>
				<br>
				{{ $t('install.error.sudo.fixInvalid') }}
				<prism-editor
					v-model='fixCommands.userSudo'
					:highlight='highlighter'
					:readonly='true'
				/>
			</div>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';


import {PrismEditor} from 'vue-prism-editor';
import 'vue-prism-editor/dist/prismeditor.min.css';
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-bash';
import 'prismjs/themes/prism.css';

@Component({
	components: {
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
	};

	/**
	 * @property {string} user User under which webapp runs
	 */
	@Prop({required: true, default: null}) user!: string;

	/**
	 * @property {boolean} exists Does sudo utility exist?
	 */
	@Prop({required: true, default: false}) exists!: boolean;

	/**
	 * @property {boolean} userSudo Can webapp execute commands with sudo?
	 */
	@Prop({required: true, default: false}) userSudo!: boolean;

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
