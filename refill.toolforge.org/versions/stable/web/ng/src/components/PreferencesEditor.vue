<template>
  <v-dialog
    v-model="dialog"
    width="500"
  >
    <v-card> 
      <v-app-bar color="primary" dark>
        <div class="v-toolbar__title">{{ msg('preferences') }}</div>
      </v-app-bar>
      <v-card-text v-if="!loading"> 
        <v-checkbox
          :label="msg('preferences-addAccessDates')"
          v-model="preferences.addAccessDates"
        ></v-checkbox>
        <v-select
          :label="msg('preferences-dateFormatEn')"
          :items="enDateFormats"
          v-model="preferences.dateFormat.en"
          variant="outlined"
        ></v-select>
      </v-card-text>
      <v-progress-linear v-else :indeterminate="true"></v-progress-linear>

      <v-card-actions>
        <v-btn variant="text" @click="save">{{ msg('preferences-save') }}</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
<script>
import Store from '../Store';

const DefaultPreferences = {
  dateFormat: {},
};

export default {
  data() {
    return {
      dialog: false,
      loading: true,
      preferences: DefaultPreferences,
      enDateFormats: ['mdy', 'dmy', 'numeric'],
    };
  },
  methods: {
    show() {
      this.dialog = true;
      this.loadPreferences();
    },
    save() {
      this.savePreferences().then(() => {
        this.dialog = false;
      });
    },
    async loadPreferences() {
      this.loading = true;
      const preferences = await Store.getItem('userpref');
      this.preferences = preferences ? Object.assign(DefaultPreferences, preferences) : DefaultPreferences;
      this.loading = false;
    },
    async savePreferences() {
      this.loading = true;
      await Store.setItem('userpref', this.preferences);
      this.loading = false;
    },
  },
}
</script>
