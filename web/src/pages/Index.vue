<template>
  <div>
    <v-container fluid grid-list-lg>
      <v-layout row wrap>
        <v-flex xs12>
          <h1>{{ msg('appname') }}<sup class="ng">&alpha;</sup></h1>
          <h2 class="tagline">{{ msg('tagline') }}</h2>
        </v-flex>
      </v-layout>

      <v-layout row wrap>
        <v-flex xs12>
          <v-card>
            <v-card-title primary-title>
              <div class="headline">{{ msg('fixwikipage') }}</div>
            </v-card-title>
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

                <v-btn fab @click="fixWikipage">
                  <v-icon>arrow_forward</v-icon>
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-flex>
      </v-layout>

      <v-layout>
        <v-flex xs12>
          <v-card>
            <v-card-title>
              <div class="headline">{{ msg('fixwikicode') }}</div>
            </v-card-title>
            <v-card-text>
              <v-textarea
                v-model="wikicode"
                :label="msg('fixwikicode-wikicode')"
              ></v-textarea>
            </v-card-text>
            <v-card-actions>
              <v-btn flat @click="fixWikicode">
                {{ msg('fixwikicode-submit') }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-flex>
      </v-layout>
    </v-container>

    <v-snackbar
      ref="taskerror"
      v-model="showError"
    >
      <span>{{ error }}</span>
    </v-snackbar>
  </div>
</template>
<script>
import Utils from '../Utils';

export default {
  data () {
    return {
      fam: 'wikipedia',
      code: 'en',
      page: '',
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
        'page': this.page,
        'fam': this.fam,
        'code': this.code
      });
    },
    fixWikicode() {
      this.submitTask('fixWikicode', {
        'wikicode': this.wikicode
      });
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
