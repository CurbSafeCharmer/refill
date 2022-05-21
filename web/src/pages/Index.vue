/* eslint-disable vue/multi-word-component-names */
<template>
  <v-flex md8 offset-md2 lg6 offset-lg3>
    <v-container fluid grid-list-lg class="mcontainer">
      <v-layout row wrap>
        <v-flex xs12>
          <h1>{{ msg('appname') }}<sup class="ng">&alpha;</sup></h1>
          <h2 class="tagline">{{ msg('tagline') }}</h2>
        </v-flex>
      </v-layout>

      <v-layout row wrap>
        <v-flex xs12>
          <v-card>
            <v-card-text>
              <div class="wikipage-form">
                <v-text-field
                  v-model="page"
                  class="page"
                  placeholder=""
                  @keyup.enter="fixWikipage"
                  :label="msg('fixwikipage-page')"
                ></v-text-field>
                <v-text-field
                  v-model="code"
                  class="code"
                  :label="msg('fixwikipage-code')"
                ></v-text-field>
                <v-text-field
                  v-model="fam"
                  class="fam"
                  :label="msg('fixwikipage-fam')"
                ></v-text-field>

                <v-btn large text outlined color="primary" icon @click="fixWikipage">
                  <v-icon>arrow_forward</v-icon>
                </v-btn>
              </div>
              <v-slide-y-transition>
                <div v-show="useCustomWikicode">
                  <v-textarea
                    v-model="wikicode"
                    :label="msg('fixwikicode-wikicode')"
                    @focus="onWikicodeFocus"
                  ></v-textarea>
                </div>
              </v-slide-y-transition>
            </v-card-text>
          </v-card>
          <v-btn text @click="useCustomWikicode = !useCustomWikicode">
            <v-icon left>{{ useCustomWikicode ? 'remove' : 'add' }}</v-icon>
            {{ msg('wikicode-toggle') }}
          </v-btn>
          <v-btn text @click="$refs.preferences.show()">
            <v-icon left>settings</v-icon>
            {{ msg('preferences-button') }}
          </v-btn>
        </v-flex>
      </v-layout>
    </v-container>

    <v-snackbar
      ref="taskerror"
      v-model="showError"
    >
      <span>{{ error }}</span>
    </v-snackbar>
    <PreferencesEditor ref="preferences"/>
  </v-flex>
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
.ng {
  color: #666;
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
