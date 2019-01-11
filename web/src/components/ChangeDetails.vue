<template>
  <v-dialog
    v-model="dialog"
    width="800px"
  >
    <v-card v-if="dialog">
      <v-toolbar color="primary" dark>
        {{ msg('report') }}
      </v-toolbar>
      <div class="shade">
        <Change :change="changes[id]"/>
      </div>
      <v-card-text>
        {{ msg('report-manual-template-intro') }}
        <pre class="pshade">* '''{{ msg('report-manual-template-taskname') }}''' {{ taskName }}
* '''{{ msg('report-manual-template-taskid') }}''' {{ taskId }}
* '''{{ msg('report-manual-template-changeid') }}''' {{ id }}
* '''{{ msg('report-manual-template-old') }}''' &lt;code&gt;&lt;nowiki&gt;{{ changes[id].old }}&lt;/nowiki&gt;&lt;/code&gt;
* '''{{ msg('report-manual-template-new') }}''' &lt;code&gt;&lt;nowiki&gt;{{ changes[id].new }}&lt;/nowiki&gt;&lt;/code&gt;</pre>
        <!--
        <p>
          {{ msg('report-intro') }}
        </p>
        <v-radio-group v-model="wikicode">
          <v-radio value="na" :label="msg('report-wikicode-na')"></v-radio>
          <v-radio value="syntax" :label="msg('report-wikicode-syntax')"></v-radio>
          <v-radio value="removal" :label="msg('report-wikicode-removal')"></v-radio>
          <span v-if="changes[id].transform === 'FillRef'">
            <v-radio value="template" :label="msg('report-wikicode-template')"></v-radio>
            <div v-if="wikicode === 'template'" class="shade">
              {{ msg('report-wikicode-template-type', changes[id].meta.type) }}
              <v-radio-group v-model="typeCorrect">
                <v-radio :value="true" :label="msg('yes')"></v-radio>
                <v-radio :value="false" :label="msg('no')"></v-radio>
                <div v-if="typeCorrect === true">
                  {{ msg('report-wikicode-template-mapinfo') }}
                </div>
                <div v-if="typeCorrect === false">
                  <v-text-field v-model="correctTemplate" :label="msg('report-wikicode-template-input')"></v-text-field>
                </div>
              </v-radio-group>
            </div>
          </span>
        </v-radio-group>
        <v-radio-group v-model="link">
          <v-radio value="na" :label="msg('report-link-na')"></v-radio>
          <v-radio value="paywall" :label="msg('report-link-paywall')"></v-radio>
          <v-radio value="dead" :label="msg('report-link-dead')"></v-radio>
          <div v-if="link === 'dead'" class="shade">
            The link now leads to:
            <v-radio-group v-model="deadLink" v-if="link === 'dead'">
              <v-radio value="error" :label="msg('report-link-dead-error')"></v-radio>
              <v-radio value="homepage" :label="msg('report-link-dead-homepage')"></v-radio>
              <v-radio value="other" :label="msg('report-link-dead-other')"></v-radio>
            </v-radio-group>
          </div>
          <v-radio value="archive" :label="msg('report-link-archive')"></v-radio>
          <div v-if="link === 'archive'" class="shade">
            <v-text-field v-model="originalUrl" :label="msg('report-link-archive-url')"></v-text-field>
          </div>
        </v-radio-group>
        Note: Not yet functional
        -->
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
<script>
import Change from './Change';

export default {
  props: ['changes', 'taskName', 'taskId'],
  components: {
    Change,
  },
  data() {
    return {
      dialog: false,
      id: false,

      link: 'na',
      deadLink: 'error',
      originalUrl: '',

      wikicode: 'na',
      typeCorrect: null,
      correctTemplate: '',
    };
  },
  methods: {
    review(id) {
      this.id = id;
      this.dialog = true;
    },
  },
};
</script>
<style lang="scss">
.shade {
  background: #eee;
  padding: 16px;
}
.pshade {
  overflow-x: scroll;
  padding: 16px;
}
</style>
