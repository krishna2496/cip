import axios from 'axios'
import store from '../store'

export default async(data) => {
    let responseData = {};
    var defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    var url = process.env.VUE_APP_API_ENDPOINT + "app/policy/listing";

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
        .catch(function(error) {
            responseData.error = true;
        });
    return responseData;
}