import axios from 'axios'
import store from '../store'

export default async() => {
    let responseData;
    var defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    await axios({
            url: process.env.VUE_APP_API_ENDPOINT + "skill",
            method: 'get',
            headers: {
                'X-localization': defaultLanguage,
                'token': store.state.token,
                'Access-Control-Allow-Origin': '*',
            }
        })
        .then((response) => {
            if(response.data.data) { 
                responseData = Object.entries(response.data.data);
            }
        })
        .catch(function(error) {});
    return responseData;
}