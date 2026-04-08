/* eslint-disable vue/multi-word-component-names */
<template>
  <v-col md="8" offset-md="2" lg="6" offset-lg="3">
    <v-container fluid class="mcontainer">
      <v-row class="flex-wrap">
        <v-col cols="12">
          <h1>{{ msg('appname') }}</h1>
          <h2 class="tagline">{{ msg('tagline') }}</h2>
        </v-col>
      </v-row>

      <v-row class="flex-wrap">
        <v-col cols="12">
          <v-card>
            <v-card-text>
              <div class="wikipage-form">
                <v-text-field
                  v-model="page"
                  class="page"
                  placeholder=""
                  @keyup.enter="fixWikipage"
                  :label="msg('fixwikipage-page')"
                  variant="outlined"
                ></v-text-field>
                <v-text-field
                  v-model="code"
                  class="code"
                  :label="msg('fixwikipage-code')"
                  variant="outlined"
                ></v-text-field>
                <v-text-field
                  v-model="fam"
                  class="fam"
                  :label="msg('fixwikipage-fam')"
                  variant="outlined"
                ></v-text-field>

                <v-btn size="large" variant="outlined" color="primary" icon @click="fixWikipage">
                  <span class="material-icons">arrow_forward</span>
                </v-btn>
              </div>
              <v-slide-y-transition>
                <div v-show="useCustomWikicode">
                  <v-textarea
                    v-model="wikicode"
                    :label="msg('fixwikicode-wikicode')"
                    @focus="onWikicodeFocus"
                    variant="outlined"
                  ></v-textarea>
                </div>
              </v-slide-y-transition>
            </v-card-text>
          </v-card>
          <v-btn variant="text" @click="useCustomWikicode = !useCustomWikicode">
            <span class="material-icons left">{{ useCustomWikicode ? 'remove' : 'add' }}</span>
            {{ msg('wikicode-toggle') }}
          </v-btn>
          <v-btn variant="text" @click="$refs.preferences.show()">
            <span class="material-icons left">settings</span>
            {{ msg('preferences-button') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <v-snackbar
      ref="taskerror"
      v-model="showError"
    >
      <span>{{ error }}</span>
    </v-snackbar>
    <PreferencesEditor ref="preferences"/>
  </v-col>
</template>
<script>
import Utils from '../Utils';
import PreferencesEditor from '../components/PreferencesEditor';

export default {
  components: {
    PreferencesEditor,
  },
  data () {
    return {
      fam: 'wikipedia',
      code: 'en',
      page: '',
      useCustomWikicode: false,
      wikicode: '<ref>http://example.com</ref>',
      error: '',
      showError: false,
    }
  },
  created () {
    this.api = this.$config.api;
  },
  methods: {
    fixWikipage() {
      this.submitTask('fixWikipage', {
        page: this.page,
        fam: this.fam,
        code: this.code,
        wikicode: this.useCustomWikicode ? this.wikicode : false,
      });
    },
    onWikicodeFocus() {
      if (!this.page.length) {
        this.page = this.msg('sandbox-page');
      }
    },
    async submitTask(action, payload) {
      try {
        const response = await Utils.submitTask(action, payload);
        this.$router.push('/result/' + action + '/' + response.data.taskId);
      } catch (e) {
        this.error = e.response.data.message;
        this.showError = true;
      }
    },
  }
}
</script>
<style lang="scss" scoped>
.mcontainer {
  margin-top: -20%;
}
.tagline {
  font-weight: normal;
}
.wikipage-form {
  display: flex;

  .page {
    flex: 6;
  }
  .code {
    flex: 1;
  }
  .fam {
    flex: 2;
  }
}
</style>
