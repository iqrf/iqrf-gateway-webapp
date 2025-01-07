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
		<v-card-title>
			<span class='mr-auto'>
				{{ $t(`iqrfnet.jsonMessage.${type}`) }}
			</span>
			<v-btn
				v-clipboard='updateMessage'
				v-clipboard:success='successMessage'
				color='primary'
				small
			>
				{{ $t(`iqrfnet.jsonMessage.copy.${type}`) }}
			</v-btn>
		</v-card-title>
		<v-card-text>
			<prism-editor
				v-model='message'
				:highlight='highlighter'
				:readonly='true'
			/>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {PrismEditor} from 'vue-prism-editor';
import 'vue-prism-editor/dist/prismeditor.min.css';
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-json';
import 'prismjs/themes/prism.css';

@Component({
	components: {
		PrismEditor
	}
})

/**
 * JSON message card
 */
export default class JsonMessage extends Vue {

	/**
	 * @property {string} message Message
	 */
	@Prop({required: true, default: null}) message!: string;

	/**
	 * @property {string} type Message type
	 */
	@Prop({required: true}) type!: string;

	/**
	 * @property {string} source Message source
	 */
	@Prop({required: true}) source!: string;

	/**
	 * Updates message in clipboard
	 * @returns {string} Message string
	 */
	private updateMessage(): string {
		return this.message;
	}

	/**
	 * Shows the success message
	 */
	private successMessage(): void {
		let message: string;
		if (this.source === 'sendDpa') {
			message = this.$t(`iqrfnet.jsonMessage.copySuccess.dpa.${this.type}`).toString();
		} else {
			message = this.$t(`iqrfnet.jsonMessage.copySuccess.json.${this.type}`).toString();
		}
		this.$toast.success(message);
	}

	/**
	 * JSON highlighter method
	 * @param {string} code text to highlight
	 */
	private highlighter(code: string) {
		return Prism.highlight(code, Prism.languages.json, 'json');
	}
}
</script>
