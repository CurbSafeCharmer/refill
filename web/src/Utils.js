import Axios from 'axios';

export default {
  submitTask(name, payload) {
    return Axios.post(`${staticConfig.api}/${name}`, payload);
  }
};
