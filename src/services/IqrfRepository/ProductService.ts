import axios, {AxiosResponse} from 'axios';

class ProductService {

	/**
	 * Retrieves the product by its HWPID
	 * @param hwpid Product HWPID
	 */
	get(hwpid: number): Promise<AxiosResponse> {
		return axios.get('https://repository.iqrfalliance.org/api/products/' + hwpid);
	}
}

export default new ProductService();
