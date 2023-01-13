<template>
	<v-card>
		<v-card-text>
			<v-alert
				v-if='cursor === null && log.length === 0'
				class='mb-0'
				type='warning'
				text
			>
				{{ $t('gateway.log.journal.notLoaded') }}
			</v-alert>
			<v-alert
				v-else-if='log.length === 0'
				class='mb-0'
				type='info'
				text
			>
				{{ $t('gateway.log.journal.noRecords') }}
			</v-alert>
			<v-alert
				v-else-if='loading'
				class='mb-0'
				type='info'
				text
			>
				{{ $t('gateway.log.journal.loading') }}
			</v-alert>
			<v-alert
				v-else-if='oldestRecords'
				class='mb-0'
				type='info'
				text
			>
				{{ $t('gateway.log.journal.noOlderRecords') }}
			</v-alert>
			<pre v-if='log.length > 0' ref='journal' v-scroll.self='scrollUpdate' class='log'>{{ log }}</pre>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {Scroll} from 'vuetify/lib/directives';

import JournalService from '@/services/JournalService';

import {AxiosError, AxiosResponse} from 'axios';
import {IJournalData} from '@/interfaces/Gateway/Journal';

/**
 * Journal viewer component
 */
@Component({
	directives: {
		Scroll,
	},
})
export default class JournalViewer extends Vue {
	/**
	 * @var {string} log Journal records content
	 */
	private log = '';

	/**
	 * @var {string|null} cursor Journal cursor
	 */
	private cursor: string|null = null;

	/**
	 * @var {boolean} allowUpdate Allow fetching of additional records
	 */
	private allowUpdate = false;

	/**
	 * @var {number} lastScrollHeight Auxiliary scrollbar height to calculate scrollbar shift
	 */
	private lastScrollHeight = 0;

	/**
	 * @var {number} lastScrollPos Last scrollbar position to return to after request failure
	 */
	private lastScrollPos = 0;

	/**
	 * @var {boolean} oldestRecords Oldest available journal records loaded
	 */
	private oldestRecords = false;

	private loading = false;

	/**
	 * Retrieves initial journal records
	 */
	mounted(): void {
		this.getJournalRecords(500);
	}

	/**
	 * Retrieves last journal records from end of journal or from specified cursor
	 * @param {number} count Number of journal records
	 * @param {string|null} cursor Journal cursor
	 */
	private getJournalRecords(count: number, cursor: string|null = null): void {
		this.allowUpdate = false;
		this.loading = true;
		JournalService.getRecords(count, cursor)
			.then((rsp: AxiosResponse) => {
				const journalData: IJournalData = rsp.data;
				if (journalData.records.length === 0) {
					this.loading = false;
					this.oldestRecords = true;
					this.allowUpdate = false;
					return;
				}
				this.log = `${journalData.records.join('\n')}\n${this.log}`;
				if (this.cursor === null) {
					this.scrollToEnd();
				} else {
					this.scrollToDisplay();
				}
				this.loading = false;
				this.cursor = journalData.startCursor;
				this.allowUpdate = true;
			})
			.catch((err: AxiosError) => {
				extendedErrorToast(err, 'gateway.log.journal.failed');
				if (this.cursor !== null) {
					this.scrollToPrevious();
				}
				this.loading = false;
				this.allowUpdate = true;
			});
	}

	/**
	 * Requests additional journal records when scrollbar reaches certain threshold
	 */
	private scrollUpdate(): void {
		if (!this.allowUpdate) {
			return;
		}
		const el = (this.$refs.journal as Element);
		if (el.scrollTop === 0) {
			this.lastScrollHeight = el.scrollHeight;
			this.lastScrollPos = el.scrollTop;
			this.getJournalRecords(500, this.cursor);
		}
	}

	/**
	 * Restores last scrollbar position before update attempt
	 */
	private scrollToPrevious(): void {
		this.$nextTick(() => {
			const el = (this.$refs.journal as Element);
			el.scrollTop = this.lastScrollPos + 1;
		});
	}

	/**
	 * Adjusts scrollbar position to preserve displayed records after update
	 */
	private scrollToDisplay(): void {
		this.$nextTick(() => {
			const el = (this.$refs.journal as Element);
			const diff = el.scrollHeight - this.lastScrollHeight;
			el.scrollTop += diff;
		});
	}

	/**
	 * Moves scrollbar to the end
	 */
	private scrollToEnd(): void {
		this.$nextTick(() => {
			const el = (this.$refs.journal as Element);
			el.scrollTop = el.scrollHeight;
		});
	}
}
</script>
