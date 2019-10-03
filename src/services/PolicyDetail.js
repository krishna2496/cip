import axios from 'axios'
import store from '../store'

export default async(slug) => {
    let responseData = {};
    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    let url = process.env.VUE_APP_API_ENDPOINT + "app/policy/" + slug;

    await axios({
            url: url,
            method: 'get',
            headers: {
                'X-localization': defaultLanguage,
                'token': store.state.token,
            }
        })
        .then((response) => {
            responseData.error = false;
            if (response.data.data) {
                responseData.data = response.data.data;
            }
        })
        .catch(function() {
            responseData.error = true;
        });
    return responseData;

}