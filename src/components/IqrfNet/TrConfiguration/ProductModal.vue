<template>
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<template #activator='{on, attrs}'>
			<v-btn
				color='primary'
				small
				v-bind='attrs'
				v-on='on'
			>
				{{ $t('iqrfnet.product.browse') }}
			</v-btn>
		</template>
		<v-card>
			<v-card-title>
				{{ $t('iqrfnet.product.title') }}
				<v-spacer />
				<v-btn
					color='primary'
					small
					@click='getProducts'
				>
					<v-icon small>
						mdi-refresh
					</v-icon>
				</v-btn>
			</v-card-title>
			<v-card-text>
				<v-row>
					<v-col cols='12' md='6'>
						<v-text-field
							v-model='filters.companyName'
							:label='$t("iqrfnet.enumeration.manufacturer")'
						/>
					</v-col>
					<v-col cols='12' md='6'>
						<v-text-field
							v-model='filters.name'
							:label='$t("iqrfnet.enumeration.product")'
						/>
					</v-col>
				</v-row>
				<v-data-table
					:loading='loading'
					:headers='headers'
					:items='items'
					mobile-breakpoint='0'
				>
					<template #[`item.actions`]='{item}'>
						<v-btn
							color='success'
							small
							@click='selectProduct(item)'
						>
							<v-icon small>
								mdi-check
							</v-icon>
						</v-btn>
					</template>
				</v-data-table>
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='closeModal'
				>
					{{ $t('forms.close') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import ModalBase from '@/components/ModalBase.vue';

import ProductService from '@/services/IqrfRepository/ProductService';

import {AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IProduct} from '@/interfaces/Repository';

/**
 * Product filters
 */
interface ProductFilters {
	companyName: string|null;
	name: string|null;
}

/**
 * Product modal component
 */
@Component
export default class ProductModal extends ModalBase {

	/**
	 * @var {boolean} loading Indicates that a request is in progress
	 */
	private loading = false;

	/**
	 * @var {Array<IProduct>} products Array of products from repository
	 */
	private products: Array<IProduct> = [];

	/**
	 * @var {ProductFilters} filters Product filters
	 */
	private filters: ProductFilters = {
		companyName: null,
		name: null,
	};

	/**
	 * @constant {Array<DataTableHeader>} headers Data table headers
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'companyName',
			text: this.$t('iqrfnet.enumeration.manufacturer').toString(),
			width: '20%',
		},
		{
			value: 'name',
			text: this.$t('iqrfnet.enumeration.product').toString(),
			width: '50%',
		},
		{
			value: 'hwpid',
			text: this.$t('iqrfnet.enumeration.hwpid').toString(),
			width: '15%',
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			sortable: false,
			filterable: false,
			align: 'end',
			width: '15%',
		},
	];

	/**
	 * Returns filtered products
	 * @return {Array<IProduct>} Filtered products
	 */
	get items(): Array<IProduct> {
		return this.products.filter((product: IProduct) => {
			return (
				(this.filters.companyName === null || product.companyName.toLowerCase().includes(this.filters.companyName.toLowerCase())) &&
				(this.filters.name === null || product.name.toLowerCase().includes(this.filters.name.toLowerCase()))
			);
		});
	}

	/**
	 * Retrieves products
	 */
	protected mounted(): void {
		this.getProducts();
	}

	/**
	 * Retrieves products from repository
	 */
	private getProducts(): void {
		this.loading = true;
		ProductService.getAll()
			.then((response: AxiosResponse) => {
				this.products = response.data;
				this.loading = false;
			})
			.catch(() => this.loading = false);
	}

	/**
	 * Selects product from table and emits data to the parent component
	 */
	private selectProduct(product: IProduct): void {
		this.closeModal();
		this.$emit('selected-product', product);
	}
}
</script>
