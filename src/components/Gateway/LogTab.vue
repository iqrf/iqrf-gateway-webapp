<template>
	<v-card>
		<v-card-text>
			<v-alert
				v-if='log === null'
				type='error'
			>
				{{ $t('gateway.log.messages.logNotFound') }}
			</v-alert>
			<v-alert
				v-else-if='log.length === 0'
				class='card-margin-bottom'
				type='info'
			>
				{{ $t('gateway.log.messages.logEmpty') }}
			</v-alert>
			<prism-editor
				v-model='log'
				:readonly='true'
				:highlight='highlighter'
				style='max-height: 75vh;'
			/>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {PrismEditor} from 'vue-prism-editor';
import 'vue-prism-editor/dist/prismeditor.min.css';
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-log';
import 'prismjs/themes/prism.css';

@Component({
	components: {
		PrismEditor,
	},
})

/**
 * Log viewer
 */
export default class LogTab extends Vue {

	/**
	 * @property {string|null} log Log content
	 */
	@Prop({required: true, default: null}) readonly log!: string|null;

	/**
	 * JSON highlighter method
	 * @param {string} code text to highlight
	 */
	private highlighter(code: string): void {
		return Prism.highlight(code, Prism.languages.log, 'log');
	}

}
</script>
