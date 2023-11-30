/* eslint-disable no-undef */
import Axios from 'axios';
import Store from './Store';

export default {
  async submitTask(name, payload) {
    const preferences = await Store.getItem('userpref');
    if (preferences) {
      payload.preferences = preferences;
    }
    return Axios.post(`${staticConfig.api}/${name}`, payload);
  },
};
