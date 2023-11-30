<template>
  <v-flex md8 offset-md2 lg6 offset-lg3 align-self-start>
    <v-card>
      <v-card-text>
        {{ msg('legacybridge-submitting') }} {{ error }}
      </v-card-text>
    </v-card>
  </v-flex>
</template>
<script>
import Utils from '../Utils';

export default {
  data() {
    return {
      error: '',
    };
  },
  async mounted() {
    try {
      const response = await Utils.submitTask('fixWikipage', {
        fam: 'wikipedia',
        page: this.$route.query.page,
        code: this.$route.query.wiki,
      });
      this.$router.replace('/result/fixWikipage/' + response.data.taskId);
    } catch (e) {
      this.error = e.response.data.message;
    }
  },
};
</script>
